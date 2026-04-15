<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
Route::get('/', function (Request $request) {
    if ($request->has('lang') && in_array($request->query('lang'), ['id', 'en'])) {
        session(['lang' => $request->query('lang')]);
    }
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/upload-media', [\App\Http\Controllers\MediaUploadController::class, 'upload'])->name('upload.media');

    // Firebase FCM token registration
    Route::post('/firebase/token', [\App\Http\Controllers\FirebaseTokenController::class, 'store'])->name('firebase.token.store');
    Route::delete('/firebase/token', [\App\Http\Controllers\FirebaseTokenController::class, 'destroy'])->name('firebase.token.destroy');

    // Diskusi (Livewire pages)
    Route::get('/dosen/classes/{kelasId}/pertemuan/{pertemuanId}/diskusi/{kelompokId}/{tahapanSintaksId?}', \App\Livewire\DiskusiDosen::class)->name('dosen.diskusi');
    Route::get('/dosen/classes/{kelasId}/pertemuan/{pertemuanId}/diskusi-kelompok/{kelompokId}/{tahapanSintaksId?}', \App\Livewire\DiskusiKelompok::class)->name('dosen.pertemuan.diskusi_kelompok');
    Route::get('/dosen/classes/{kelasId}/pertemuan/{pertemuanId}/diskusi-kelas/{tahapanSintaksId?}', \App\Livewire\DiskusiKelas::class)->name('dosen.diskusi_kelas');
    
    Route::get('/mahasiswa/classes/{kelasId}/pertemuan/{pertemuanId}/diskusi/{tahapanSintaksId?}', \App\Livewire\DiskusiKelompok::class)->name('mahasiswa.diskusi');
    Route::get('/mahasiswa/classes/{kelasId}/pertemuan/{pertemuanId}/diskusi-kelas/{tahapanSintaksId?}', \App\Livewire\DiskusiKelas::class)->name('mahasiswa.diskusi_kelas');

    Route::get('/dashboard', function(Request $request) {
        $role = $request->user()->role;
        if ($role === 'admin') return redirect('/admin/dashboard');
        if ($role === 'dosen') return redirect('/dosen/dashboard');
        return redirect('/mahasiswa/dashboard');
    });

    Route::get('/admin/dashboard', \App\Livewire\Admin\AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', \App\Livewire\Admin\UserManagement::class)->name('admin.users');
    Route::get('/admin/layout', \App\Livewire\Admin\LayoutCustomization::class)->name('admin.layout');
    
    // Support & FAQ (User)
    Route::get('/support/tickets', \App\Livewire\HelpTicketUser::class)->name('support.tickets');
    Route::get('/support/faq', \App\Livewire\FaqPage::class)->name('support.faq');
    
    // Academic & Reporting Routes (Phase 5)
    Route::get('/admin/periods', \App\Livewire\Admin\PeriodManager::class)->name('admin.periods');
    Route::get('/admin/academic-audit', \App\Livewire\Admin\AcademicAudit::class)->name('admin.academic-audit');
    Route::get('/admin/grade-recap', \App\Livewire\Admin\GradeRecap::class)->name('admin.grade-recap');
    Route::get('/admin/data-manager', \App\Livewire\Admin\AcademicDataManager::class)->name('admin.data-manager');

    // Admin Support
    Route::get('/admin/tickets', \App\Livewire\Admin\HelpTicketAdmin::class)->name('admin.tickets');
    Route::get('/admin/faq', \App\Livewire\Admin\FaqManager::class)->name('admin.faq');
    Route::get('/dosen/dashboard', \App\Livewire\Lecturer\Dashboard::class);
    Route::get('/dosen/classes', \App\Livewire\DosenDashboard::class)->name('dosen.classes');
    Route::get('/dosen/kelompok', \App\Livewire\DosenKelompok::class)->name('dosen.kelompok.manager');
    Route::get('/dosen/presensi', \App\Livewire\LecturerAttendance::class)->name('dosen.presensi.manager');
    Route::get('/dosen/rekap-nilai', \App\Livewire\LecturerGradeRecap::class)->name('dosen.rekap-nilai');
    Route::get('/dosen/bank-soal', \App\Livewire\Dosen\BankSoalManager::class)->name('dosen.bank-soal');
    Route::get('/dosen/diskusi/{kelasId?}', \App\Livewire\DiscussionHub::class)->name('dosen.diskusi.hub');
    Route::get('/dosen/ujians/{ujianId}/soals', \App\Livewire\Dosen\ManageSoalUjian::class)->name('dosen.ujians.soals');
    Route::get('/dosen/ujians/{ujianId}/manual', \App\Livewire\Dosen\ManualGrading::class)->name('dosen.ujians.manual');
    Route::get('/dosen/ujians/{ujianId}/hasil', \App\Livewire\Dosen\ManageHasilUjian::class)->name('dosen.ujians.hasil');
    Route::get('/dosen/ujians/{ujianId}/hasil/{mahasiswaId}', \App\Livewire\Dosen\DetailHasilUjian::class)->name('dosen.ujians.hasil.detail');
    Route::get('/dosen/classes/{id}', \App\Livewire\LecturerClassDetail::class);
    Route::get('/dosen/classes/{kelasId}/grading-settings', \App\Livewire\LecturerGradingSettings::class)->name('dosen.grading-settings');
    Route::get('/dosen/classes/{kelasId}/flow-builder/{pertemuanId?}', \App\Livewire\Dosen\FlowBuilder::class)->name('dosen.flow-builder');
    Route::get('/mahasiswa/dashboard', \App\Livewire\StudentDashboard::class);
    Route::get('/mahasiswa/classes', \App\Livewire\MahasiswaClasses::class);
    Route::get('/mahasiswa/classes/{id}', \App\Livewire\StudentClassDetail::class);
    Route::get('/mahasiswa/classes/{kelasId}/pertemuan/{pertemuanId}/flow', \App\Livewire\Mahasiswa\LearningFlow::class)->name('mahasiswa.learning-flow');
    Route::get('/mahasiswa/materi-eksternal', \App\Livewire\Mahasiswa\ExternalMateriList::class);
    Route::get('/mahasiswa/ujians', \App\Livewire\Mahasiswa\UjianMahasiswa::class)->name('mahasiswa.ujians');
    Route::get('/mahasiswa/ujians/{ujianId}/take', \App\Livewire\Mahasiswa\TakeUjian::class)->name('mahasiswa.ujians.take');
    Route::get('/mahasiswa/soal/{masterSoalId}', \App\Livewire\StudentDoAssignment::class)->name('mahasiswa.soal');
    Route::get('/mahasiswa/diskusi/{kelasId?}', \App\Livewire\DiscussionHub::class)->name('mahasiswa.diskusi.hub');
    
    // AI Assistant
    Route::get('/ai-assistant', \App\Livewire\TanyaAiChat::class)->name('ai-assistant');
    
    // Chatbot API
    Route::post('/api/ai/chat', [\App\Http\Controllers\Api\ChatbotController::class, 'sendMessage'])->name('api.ai.chat');
});
