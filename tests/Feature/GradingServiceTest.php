<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\SintaksBelajar;
use App\Models\TahapanSintaks;
use App\Models\MasterSoal;
use App\Models\MainSoal;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\SiteSetting;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $gradingService;
    protected $class;
    protected $studentUser;
    protected $studentData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradingService = new GradingService();

        // 1. Setup Dosen & Course
        $dosenUserId = DB::table('users')->insertGetId([
            'name' => 'Test Dosen',
            'email' => 'dosen@test.com',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dosenId = (string) Str::uuid();
        DB::table('dosens')->insert([
            'dosen_id' => $dosenId,
            'user_id' => $dosenUserId,
            'nama' => 'Test Dosen',
            'kode' => 'DSN001',
            'no_wa' => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = (string) Str::uuid();
        DB::table('mata_kuliah')->insert([
            'mata_kuliah_id' => $courseId,
            'mata_kuliah' => 'Test Course',
            'kode' => 'TEST101',
            'dosen_id' => $dosenId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->class = Kelas::create([
            'kelas_id' => (string) Str::uuid(),
            'kelas' => 'Testing Class',
            'kode' => 'TEST-001',
            'mata_kuliah_id' => $courseId,
        ]);

        // 2. Setup Student
        $studentUserId = DB::table('users')->insertGetId([
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->studentUser = User::find($studentUserId);

        $mahasiswaId = (string) Str::uuid();
        DB::table('mahasiswas')->insert([
            'mahasiswa_id' => $mahasiswaId,
            'user_id' => $studentUserId,
            'nama' => 'Test Student',
            'nim' => '12345',
            'angkatan' => '2023',
            'program_studi' => 'Informatika',
            'no_wa' => '08987654321',
            'foto' => 'default.jpg',
            'is_active' => true,
            'kode_verifikasi' => '123456',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->studentData = DB::table('mahasiswas')->where('mahasiswa_id', $mahasiswaId)->first();
    }

    /** @test */
    public function it_calculates_attendance_score_correctly()
    {
        // Setup 4 meetings
        for ($i = 1; $i <= 4; $i++) {
            Pertemuan::create([
                'pertemuan_id' => (string) Str::uuid(),
                'kelas_id' => $this->class->kelas_id,
                'pertemuan' => "Pertemuan $i"
            ]);
        }

        $meetings = Pertemuan::all();

        // Present in 3 out of 4 (75%)
        foreach ($meetings->take(3) as $m) {
            DB::table('presensi_mahasiswa')->insert([
                'presensi_id' => (string) Str::uuid(),
                'mahasiswa_id' => $this->studentData->mahasiswa_id,
                'pertemuan_id' => $m->pertemuan_id,
                'status' => 'hadir',
                'waktu_presensi' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $score = $this->gradingService->calculateAttendanceScore($this->class->kelas_id, $this->studentUser->id);
        
        $this->assertEquals(75, $score);
    }

    /** @test */
    public function it_calculates_weighted_average_from_tasks_level_2_and_3()
    {
        // 1. Setup Structure (Pertemuan > Sintaks > Tahapan)
        $pertemuan = Pertemuan::create([
            'pertemuan_id' => (string) Str::uuid(),
            'kelas_id' => $this->class->kelas_id,
            'pertemuan' => 'Pertemuan Tugas'
        ]);
        $sintaks = SintaksBelajar::create([
            'sintaks_belajar_id' => (string) Str::uuid(),
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'model_pembelajaran' => 'PBL',
            'sintaks_belajar' => 'Custom'
        ]);
        $tahapan = TahapanSintaks::create([
            'tahapan_sintaks_id' => (string) Str::uuid(),
            'sintaks_belajar_id' => $sintaks->sintaks_belajar_id,
            'nama_tahapan' => 'Tahap Tugas',
            'urutan' => 1
        ]);

        // 2. Create 2 Task Items with different Weights (Level 2)
        $taskA = MasterSoal::create([
            'master_soal_id' => (string) Str::uuid(),
            'tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id,
            'master_soal' => 'Tugas A',
            'bobot' => 20
        ]);
        $taskB = MasterSoal::create([
            'master_soal_id' => (string) Str::uuid(),
            'tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id,
            'master_soal' => 'Tugas B',
            'bobot' => 10
        ]);

        // 3. Setup Questions for Task A (Level 3)
        $msA = MainSoal::create(['main_soal_id' => (string) Str::uuid(), 'master_soal_id' => $taskA->master_soal_id, 'main_soal' => 'Blok A']);
        $sA1 = Soal::create(['soal_id' => (string) Str::uuid(), 'main_soal_id' => $msA->main_soal_id, 'soal' => 'Q1', 'bobot' => 5]);
        $sA2 = Soal::create(['soal_id' => (string) Str::uuid(), 'main_soal_id' => $msA->main_soal_id, 'soal' => 'Q2', 'bobot' => 15]);

        DB::table('jawaban_mahasiswa')->insert([
            ['jawaban_id' => (string) Str::uuid(), 'soal_id' => $sA1->soal_id, 'master_soal_id' => $taskA->master_soal_id, 'user_id' => $this->studentUser->id, 'nilai' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['jawaban_id' => (string) Str::uuid(), 'soal_id' => $sA2->soal_id, 'master_soal_id' => $taskA->master_soal_id, 'user_id' => $this->studentUser->id, 'nilai' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Setup Question for Task B (Level 3)
        $msB = MainSoal::create(['main_soal_id' => (string) Str::uuid(), 'master_soal_id' => $taskB->master_soal_id, 'main_soal' => 'Blok B']);
        $sB1 = Soal::create(['soal_id' => (string) Str::uuid(), 'main_soal_id' => $msB->main_soal_id, 'soal' => 'Q1', 'bobot' => 10]);
        DB::table('jawaban_mahasiswa')->insert([
            ['jawaban_id' => (string) Str::uuid(), 'soal_id' => $sB1->soal_id, 'master_soal_id' => $taskB->master_soal_id, 'user_id' => $this->studentUser->id, 'nilai' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $categoryAverages = $this->gradingService->getCategoryAverages($this->class->kelas_id, $this->studentUser->id);
        $this->assertEquals(66.67, round($categoryAverages['tugas'], 2));
    }

    /** @test */
    public function it_calculates_final_grade_incorporating_level_1_weights()
    {
        $dosenId = $this->class->mataKuliah->dosen_id;
        $courseId = $this->class->mata_kuliah_id;

        // Setup Weights: Presensi 10%, Tugas 40%, UTS 50%
        DB::table('grading_weights')->insert([
            ['id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'presensi', 'weight' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'tugas', 'weight' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'category' => 'uts', 'weight' => 50, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 1. Attendance: 100%
        Pertemuan::create(['pertemuan_id' => (string) Str::uuid(), 'kelas_id' => $this->class->kelas_id, 'pertemuan' => 'P1']);
        DB::table('presensi_mahasiswa')->insert([
            'presensi_id' => (string) Str::uuid(), 'mahasiswa_id' => $this->studentData->mahasiswa_id, 'pertemuan_id' => Pertemuan::where('kelas_id', $this->class->kelas_id)->first()->pertemuan_id, 'status' => 'hadir', 'waktu_presensi' => now(), 'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. Mock Tugas: 80%
        $pertemuan = Pertemuan::where('kelas_id', $this->class->kelas_id)->first();
        $sintaks = SintaksBelajar::create(['sintaks_belajar_id' => (string) Str::uuid(), 'pertemuan_id' => $pertemuan->pertemuan_id, 'model_pembelajaran' => 'PBL', 'sintaks_belajar' => 'Custom']);
        $tahapan = TahapanSintaks::create(['tahapan_sintaks_id' => (string) Str::uuid(), 'sintaks_belajar_id' => $sintaks->sintaks_belajar_id, 'nama_tahapan' => 'T1', 'urutan' => 1]);
        $task = MasterSoal::create(['master_soal_id' => (string) Str::uuid(), 'tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id, 'master_soal' => 'T', 'bobot' => 100]);
        $ms = MainSoal::create(['main_soal_id' => (string) Str::uuid(), 'master_soal_id' => $task->master_soal_id, 'main_soal' => 'B']);
        $soal = Soal::create(['soal_id' => (string) Str::uuid(), 'main_soal_id' => $ms->main_soal_id, 'soal' => 'Q', 'bobot' => 100]);
        DB::table('jawaban_mahasiswa')->insert(['jawaban_id' => (string) Str::uuid(), 'soal_id' => $soal->soal_id, 'master_soal_id' => $task->master_soal_id, 'user_id' => $this->studentUser->id, 'nilai' => 80, 'created_at' => now(), 'updated_at' => now()]);

        // 3. Mock UTS (Ujian): 60%
        $ujian = Ujian::create([
            'ujian_id' => (string) Str::uuid(),
            'kelas_id' => $this->class->kelas_id,
            'nama_ujian' => 'UTS',
            'jenis_ujian' => 'uts',
            'jumlah_soal' => 1,
            'mode_batasan' => 'open',
            'bobot_nilai' => 100,
            'mata_kuliah_id' => $courseId,
            'dosen_id' => $dosenId,
            'waktu_mulai' => now(),
            'waktu_selesai' => now()->addHour(),
        ]);
        DB::table('soal_ujians')->insert([
            'soal_id' => (string) Str::uuid(), 'ujian_id' => $ujian->ujian_id, 'soal' => 'Q Ujian', 'bobot' => 100, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('nilai_ujians_mahasiswa')->insert([
            'nilai_id' => (string) Str::uuid(),
            'ujian_id' => $ujian->ujian_id, 
            'mahasiswa_id' => $this->studentData->mahasiswa_id,
            'kelas_id' => $this->class->kelas_id,
            'mata_kuliah_id' => $courseId,
            'dosen_id' => $dosenId,
            'nilai' => 60,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $finalGrade = $this->gradingService->calculateFinalGrade($this->class->kelas_id, $this->studentUser->id);
        $this->assertEquals(72, $finalGrade);
    }
}
