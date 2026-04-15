<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Ujian;
use App\Models\MasterSoal;
use App\Models\JawabanMahasiswa;
use App\Models\SiteSetting;
use App\Models\GradingComponent;
use Illuminate\Support\Facades\DB;

class GradingService
{
    public function getActiveComponents($classId)
    {
        $components = GradingComponent::where('kelas_id', $classId)->get();

        if ($components->isEmpty()) {
            // Seed defaults if classes do not have them yet (Lazy setup)
            $defaultJson = SiteSetting::get('default_grading_weights', json_encode([
                'tugas' => 25, 'uts' => 25, 'uas' => 30, 'kuis' => 10, 'presensi' => 10
            ]));
            $weights = json_decode($defaultJson, true);
            
            foreach ($weights as $category => $weight) {
                $mappingType = 'exam';
                if ($category === 'tugas') $mappingType = 'assignment';
                if ($category === 'presensi') $mappingType = 'attendance';

                GradingComponent::create([
                    'kelas_id' => $classId,
                    'name' => ucfirst($category),
                    'category' => $category,
                    'weight' => $weight,
                    'is_default' => true,
                    'mapping_type' => $mappingType
                ]);
            }
            $components = GradingComponent::where('kelas_id', $classId)->get();
        }

        return $components;
    }

    public function getActiveWeights($classId)
    {
        // Legacy support wrapper
        $components = $this->getActiveComponents($classId);
        return $components->pluck('weight', 'category')->toArray();
    }

    public function calculateFinalGrade($classId, $userId, $scores = null)
    {
        $components = $this->getActiveComponents($classId);
        $scores = $scores ?: $this->getCategoryAverages($classId, $userId);

        $finalGrade = 0;
        foreach ($components as $comp) {
            // Priority 1: Use the average score indexed by component ID
            $categoryScore = $scores[$comp->id]['score'] ?? 0;
            $finalGrade += ($categoryScore * ($comp->weight / 100));
        }

        return round($finalGrade, 2);
    }

    public function getCategoryAverages($classId, $userId)
    {
        $averages = [];
        $components = $this->getActiveComponents($classId);

        foreach ($components as $component) {
            $score = 0;
            switch ($component->mapping_type) {
                case 'assignment':
                    $score = $this->calculateAssignmentScore($classId, $userId, $component);
                    break;
                case 'exam':
                case 'manual':
                    $score = $this->calculateExamScore($classId, $userId, $component);
                    break;
                case 'attendance':
                    $score = $this->calculateAttendanceScore($classId, $userId);
                    break;
            }

            $averages[$component->id] = [
                'component_id' => $component->id,
                'name' => $component->name,
                'category' => $component->category, // for backward compat
                'weight' => $component->weight,
                'score' => $score
            ];
            
            // Populate legacy string keys as well to avoid breaking old views abruptly
            if (!empty($component->category)) {
                $averages[$component->category] = $score;
            }
        }

        return $averages;
    }

    private function calculateAssignmentScore($classId, $userId, $component)
    {
        // 1. Get assignments linked to this component OR fallback to legacy "tugas" type if component is default
        $query = DB::table('pertemuans')
            ->join('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
            ->join('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
            ->join('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
            ->where('pertemuans.kelas_id', $classId);

        // Dynamic mapping or Legacy Fallback (Match by ID OR Match by Type if orphan)
        $query->where(function ($q) use ($component) {
            $q->where('master_soal.grading_component_id', $component->id);
            
            // Fallback: If item has no component_id, map to any component with correct mapping_type
            if ($component->mapping_type === 'assignment') {
                $q->orWhereNull('master_soal.grading_component_id');
            }
        });

        $tugasIds = $query->pluck('master_soal.master_soal_id');

        if ($tugasIds->isNotEmpty()) {
            return $this->calculateAverageFromMasterSoal($tugasIds, $userId);
        }
        return 0;
    }

    private function calculateExamScore($classId, $userId, $component)
    {
        // Find Ujians mapped to this component or fallback
        $query = Ujian::where('kelas_id', $classId)
            ->where(function($q) use ($component) {
                $q->where('grading_component_id', $component->id);
                
                // Legacy Fallback: Match by type if orphan
                $q->orWhere(function($subq) use ($component) {
                    $subq->whereNull('grading_component_id')
                         ->where('jenis_ujian', $component->category);
                });
            });

        $ujianIds = $query->pluck('ujian_id');

        if ($ujianIds->isNotEmpty()) {
            return $this->calculateAverageFromUjian($ujianIds, $userId);
        }
        return 0;
    }

    private function calculateAverageFromMasterSoal($masterSoalIds, $userId)
    {
        $items = MasterSoal::whereIn('master_soal_id', $masterSoalIds)->get();

        if ($items->isEmpty()) return 0;

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $maxPoints = DB::table('soal')
                ->join('main_soal', 'soal.main_soal_id', '=', 'main_soal.main_soal_id')
                ->where('main_soal.master_soal_id', $item->master_soal_id)
                ->sum('soal.bobot');

            if ($maxPoints <= 0) $maxPoints = 100;

            $userRawScore = DB::table('jawaban_mahasiswa')
                ->where('master_soal_id', $item->master_soal_id)
                ->where('user_id', $userId)
                ->sum('nilai'); 

            $normalizedItemScore = ($userRawScore / $maxPoints) * 100;

            $itemWeight = (float) ($item->bobot ?: 1);
            $totalWeightedScore += ($normalizedItemScore * $itemWeight);
            $totalWeight += $itemWeight;
        }

        return $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) : 0;
    }

    private function calculateAverageFromUjian($ujianIds, $userId)
    {
        $items = Ujian::whereIn('ujian_id', $ujianIds)->get();

        if ($items->isEmpty()) return 0;

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $maxPoints = $item->soalUjians()->sum('bobot');

            if ($maxPoints <= 0) {
                // If Manual Mapping or No questions, check if we have a direct nilai_ujians_mahasiswa as manual score out of 100
                $maxPoints = 100;
            }

            $userRawScore = DB::table('nilai_ujians_mahasiswa')
                ->join('mahasiswas', 'nilai_ujians_mahasiswa.mahasiswa_id', '=', 'mahasiswas.mahasiswa_id')
                ->where('nilai_ujians_mahasiswa.ujian_id', $item->ujian_id)
                ->where('mahasiswas.user_id', $userId)
                ->value('nilai_ujians_mahasiswa.nilai') ?? 0;

            $normalizedItemScore = ($userRawScore / $maxPoints) * 100;

            $itemWeight = (float) ($item->bobot_nilai ?: 1);
            $totalWeightedScore += ($normalizedItemScore * $itemWeight);
            $totalWeight += $itemWeight;
        }

        return $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) : 0;
    }

    public function calculateAttendanceScore($classId, $userId)
    {
        $mahasiswa = DB::table('mahasiswas')->where('user_id', $userId)->first();
        if (!$mahasiswa) return 0;

        $totalMeetings = DB::table('pertemuans')
            ->where('kelas_id', $classId)
            ->count();

        if ($totalMeetings === 0) return 100;

        $attendedCount = DB::table('presensi_mahasiswa')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('pertemuan_id', function($query) use ($classId) {
                $query->select('pertemuan_id')->from('pertemuans')->where('kelas_id', $classId);
            })
            ->where('status', 'hadir')
            ->count();

        return ($attendedCount / $totalMeetings) * 100;
    }
}
