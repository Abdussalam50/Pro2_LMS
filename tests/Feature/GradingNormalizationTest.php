<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Ujian;
use App\Models\MahasiswaData;
use App\Services\GradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class GradingNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $student;
    protected $class;
    protected $periodId;
    protected $mkId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradingService();
        
        // 1. Setup Academic Period
        $this->periodId = (string) Str::uuid();
        DB::table('academic_periods')->insert([
            'id' => $this->periodId,
            'name' => '2026/2027 Ganjil',
            'tahun' => '2026',
            'semester' => 'ganjil',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Setup Mata Kuliah
        $this->mkId = (string) Str::uuid();
        $dosenId = (string) Str::uuid();
        DB::table('mata_kuliah')->insert([
            'mata_kuliah_id' => $this->mkId,
            'mata_kuliah' => 'Mekanika Klasik',
            'kode' => 'MK101',
            'dosen_id' => $dosenId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Setup Class
        $this->class = Kelas::create([
            'kelas_id' => (string) Str::uuid(),
            'kelas' => 'Mekanika (Kelas A)',
            'kode' => 'MEK101',
            'mata_kuliah_id' => $this->mkId,
            'academic_period_id' => $this->periodId
        ]);

        // 4. Setup Student
        $user = User::create([
            'name' => 'Mahasiswa Teladan',
            'email' => 'teladan@test.com',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa'
        ]);

        $this->student = MahasiswaData::create([
            'mahasiswa_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'nim' => '123456789'
        ]);

        // 5. Setup Weights (40% Tugas, 30% UTS, 30% UAS)
        DB::table('grading_weights')->insert([
            ['grading_weight_id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'tugas', 'weight' => 40],
            ['grading_weight_id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'uts', 'weight' => 30],
            ['grading_weight_id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'uas', 'weight' => 30],
        ]);
    }

    /** @test */
    public function it_normalizes_ujian_score_to_40_percent_from_8_of_20()
    {
        // Create Ujian (UTS)
        $ujian = Ujian::create([
            'ujian_id' => (string) Str::uuid(),
            'kelas_id' => $this->class->kelas_id,
            'nama_ujian' => 'UTS Mekanika',
            'jenis_ujian' => 'uts',
            'bobot_nilai' => 10,
            'mata_kuliah_id' => $this->mkId,
            'dosen_id' => (string) Str::uuid(),
            'jumlah_soal' => 1,
            'mode_batasan' => 'bebas'
        ]);

        // Create 1 question with bobot 20
        DB::table('soal_ujians')->insert([
            'soal_id' => (string) Str::uuid(),
            'ujian_id' => $ujian->ujian_id,
            'soal' => 'Problem 1',
            'bobot' => 20
        ]);

        // Student gets 8 points (out of 20) -> MUST be 40%
        DB::table('nilai_ujians_mahasiswa')->insert([
            'nilai_ujian_id' => (string) Str::uuid(),
            'ujian_id' => $ujian->ujian_id,
            'mahasiswa_id' => $this->student->mahasiswa_id,
            'nilai' => 8
        ]);

        $averages = $this->service->getCategoryAverages($this->class->kelas_id, $this->student->user_id);
        
        $this->assertEquals(40.0, (float)$averages['uts']);
    }

    /** @test */
    public function it_calculates_final_grade_correctly_based_on_verified_weights()
    {
        // Setup UTS (8/20 = 40%)
        $ujian = Ujian::create([
            'ujian_id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'nama_ujian' => 'UTS',
            'jenis_ujian' => 'uts', 'bobot_nilai' => 1, 'mata_kuliah_id' => $this->mkId, 'dosen_id' => (string) Str::uuid(),
            'jumlah_soal' => 1, 'mode_batasan' => 'bebas'
        ]);
        DB::table('soal_ujians')->insert([
            'soal_id' => (string) Str::uuid(), 'ujian_id' => $ujian->ujian_id, 'soal' => 'Q1', 'bobot' => 20
        ]);
        DB::table('nilai_ujians_mahasiswa')->insert([
            'nilai_ujian_id' => (string) Str::uuid(), 'ujian_id' => $ujian->ujian_id, 'mahasiswa_id' => $this->student->mahasiswa_id, 'nilai' => 8
        ]);

        // Expected Final Grade: 40.0 * 0.3 = 12.0
        $finalGrade = $this->service->calculateFinalGrade($this->class->kelas_id, $this->student->user_id);
        
        $this->assertEquals(12.0, (float)$finalGrade);
    }
}
