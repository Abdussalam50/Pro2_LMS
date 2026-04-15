<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\MahasiswaData;
use App\Models\NilaiUjianMahasiswa;
use App\Models\JawabanUjianMahasiswa;
use App\Services\GeminiAiService;
use App\Services\ClaudeAiService;
use Illuminate\Support\Facades\Auth;

class DetailHasilUjian extends Component
{
    public $ujian;
    public $mahasiswa;
    public $nilai;
    public $jawabans;
    public $grades = [];
    public $aiReviews = []; // Store AI analysis per jawaban_id

    public function getAiReviewForExam($jawabanId)
    {
        // Resolve Service based on config
        $provider = config('ai.provider', 'gemini');
        $aiService = ($provider === 'claude') ? app(ClaudeAiService::class) : app(GeminiAiService::class);

        $jawaban = JawabanUjianMahasiswa::with('soal')->find($jawabanId);

        if (!$jawaban || !$jawaban->soal) {
            session()->flash('error', 'Data tidak ditemukan.');
            $this->dispatch('swal', ['title' => 'Error!', 'text' => 'Data tidak ditemukan.', 'icon' => 'error']);
            return;
        }

        $this->aiReviews[$jawabanId] = ['loading' => true];

        try {
            $review = $aiService->reviewEssay(
                $jawaban->soal->soal,
                $jawaban->soal->jawaban_esai ?? 'Tidak ada kunci jawaban.',
                $jawaban->jawaban_esai,
                $jawaban->soal->bobot ?? 100
            );

            if (isset($review['error'])) {
                $this->aiReviews[$jawabanId] = ['error' => $review['error']];
            } else {
                $this->aiReviews[$jawabanId] = $review;
            }
        } catch (\Exception $e) {
            $this->aiReviews[$jawabanId] = ['error' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function applyAiReview($jawabanId)
    {
        if (isset($this->aiReviews[$jawabanId]) && !isset($this->aiReviews[$jawabanId]['error'])) {
            $this->grades[$jawabanId] = $this->aiReviews[$jawabanId]['suggested_score'];
            $this->saveGrade($jawabanId); // Re-use existing save logic
        }
    }

    public function mount($ujianId, $mahasiswaId)
    {
        $this->ujian = Ujian::with(['mataKuliah', 'kelas'])
            ->where('ujian_id', $ujianId)
            ->firstOrFail();

        $this->mahasiswa = MahasiswaData::with('user')
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();

        $this->nilai = NilaiUjianMahasiswa::where('ujian_id', $ujianId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->first();

        // Check access
        $user = Auth::user();
        if ($user->role !== 'dosen' || $this->ujian->dosen_id !== $user->dosenData->dosen_id) {
             abort(403);
        }

        $this->jawabans = JawabanUjianMahasiswa::with('soal')
            ->where('ujian_id', $ujianId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->get();
            
        foreach($this->jawabans as $j) {
            $this->grades[$j->jawaban_id] = $j->skor ?? 0;
        }
    }

    public function saveGrade($jawabanId)
    {
        $j = JawabanUjianMahasiswa::with('soal')->find($jawabanId);

        // Clamp skor: tidak boleh negatif, tidak boleh melebihi bobot soal
        $maxBobot = $j->soal->bobot ?? 100;
        $skor = max(0, min((float) ($this->grades[$jawabanId] ?? 0), $maxBobot));

        $j->update(['skor' => $skor]);
        $this->grades[$jawabanId] = $skor; // sync state
        
        $this->calculateFinalScore();
        session()->flash('message', 'Skor berhasil diperbarui.');
        $this->dispatch('swal', ['title' => 'Tersimpan!', 'text' => 'Skor berhasil diperbarui.', 'icon' => 'success']);
    }

    public function calculateFinalScore()
    {
        $totalSkor = JawabanUjianMahasiswa::where('ujian_id', $this->ujian->ujian_id)
            ->where('mahasiswa_id', $this->mahasiswa->mahasiswa_id)
            ->sum('skor');
        
        if ($this->nilai) {
            $this->nilai->update(['nilai' => $totalSkor]);
        }
    }

    public function render()
    {
        return view('livewire.dosen.detail-hasil-ujian')
            ->layout('components.layout');
    }
}
