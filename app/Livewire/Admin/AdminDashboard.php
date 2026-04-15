<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Pertemuan;
use App\Models\Ujian;
use App\Models\JawabanMahasiswa;
use App\Models\PresensiMahasiswa;
use App\Models\EnrollmentRequest;
use App\Models\HelpTicket;
use App\Models\Feedback;
use Carbon\Carbon;

class AdminDashboard extends Component
{
    // Stats Cards
    public int $totalMahasiswa = 0;
    public int $totalDosen = 0;
    public int $totalKelas = 0;
    public int $totalMataKuliah = 0;
    public int $pendingUsers = 0;
    public int $pendingEnrollments = 0;
    public int $totalPertemuan = 0;
    public int $totalUjian = 0;
    public int $pendingTickets = 0;
    public float $averageRating = 0.0;
    public int $totalFeedback = 0;

    // Chart Data
    public array $registrationTrend = [];
    public array $userRoleDistribution = [];
    public array $kelasPopulation = [];

    // Recent Activity
    public array $recentUsers = [];
    public array $recentPendingUsers = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadChartData();
        $this->loadRecentActivity();
    }

    public function loadStats()
    {
        $this->totalMahasiswa = User::where('role', 'mahasiswa')->where('is_active', true)->count();
        $this->totalDosen = User::where('role', 'dosen')->where('is_active', true)->count();
        $this->totalKelas = Kelas::count();
        $this->totalMataKuliah = MataKuliah::count();
        $this->pendingUsers = User::where('is_active', false)->count();
        $this->pendingEnrollments = EnrollmentRequest::where('status', 'pending')->count();
        $this->totalPertemuan = Pertemuan::count();
        $this->totalUjian = Ujian::count();
        $this->pendingTickets = HelpTicket::where('status', '!=', 'closed')->count();
        $this->totalFeedback = Feedback::count();
        $this->averageRating = (float) (Feedback::avg('rating') ?? 0);
    }

    public function loadChartData()
    {
        // Registration trend (last 12 months)
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $months->push([
                'label' => $date->translatedFormat('M Y'),
                'count' => $count,
            ]);
        }
        $this->registrationTrend = $months->toArray();

        // User role distribution
        $this->userRoleDistribution = [
            ['role' => 'Admin', 'count' => User::where('role', 'admin')->count()],
            ['role' => 'Dosen', 'count' => User::where('role', 'dosen')->count()],
            ['role' => 'Mahasiswa', 'count' => User::where('role', 'mahasiswa')->count()],
        ];

        // Top 8 kelas by student count
        $this->kelasPopulation = Kelas::withCount('mahasiswas')
            ->with('mataKuliah')
            ->orderByDesc('mahasiswas_count')
            ->take(8)
            ->get()
            ->map(fn($k) => [
                'name' => $k->kelas,
                'course' => $k->mataKuliah->mata_kuliah ?? '-',
                'count' => $k->mahasiswas_count,
            ])
            ->toArray();
    }

    public function loadRecentActivity()
    {
        // Most recent 5 registered users
        $this->recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => $u->is_active,
                'created_at' => $u->created_at->diffForHumans(),
            ])
            ->toArray();

        // Pending users
        $this->recentPendingUsers = User::where('is_active', false)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => $u->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function quickApproveUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['is_active' => true]);
            $this->loadStats();
            $this->loadRecentActivity();
            $this->dispatch('swal', ['title' => 'Disetujui!', 'text' => $user->name . ' telah diaktifkan.', 'icon' => 'success']);
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard')
            ->layout('components.layout');
    }
}
