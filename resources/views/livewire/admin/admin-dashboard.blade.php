<div>
    <x-slot name="title">Admin Dashboard - Pro2Lms</x-slot>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Dashboard Admin</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan data & analitik LMS Anda</p>
    </div>

    {{-- ============================================================ --}}
    {{-- STATS GRID                                                    --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5 mb-8">
        {{-- Total Mahasiswa --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-indigo-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Mahasiswa Aktif</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($totalMahasiswa) }}</p>
            </div>
        </div>

        {{-- Total Dosen --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-emerald-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="briefcase" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Dosen Aktif</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($totalDosen) }}</p>
            </div>
        </div>

        {{-- Total Kelas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-amber-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="school" class="w-5 h-5 text-amber-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Kelas Aktif</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($totalKelas) }}</p>
            </div>
        </div>

        {{-- Total Mata Kuliah --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-violet-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-violet-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="book-copy" class="w-5 h-5 text-violet-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Mata Kuliah</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($totalMataKuliah) }}</p>
            </div>
        </div>

        {{-- Pending Tickets --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-rose-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-rose-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="ticket" class="w-5 h-5 text-rose-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Tiket Pending</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($pendingTickets) }}</p>
            </div>
        </div>

        {{-- Average Rating --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:border-amber-100 transition-all">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                    <i data-lucide="star" class="w-5 h-5 text-amber-600"></i>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Rating LMS</p>
                <p class="text-2xl md:text-3xl font-black text-gray-900">{{ number_format($averageRating, 1) }}<span class="text-sm text-gray-400 font-bold">/5</span></p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ALERT CARDS (PENDING)                                         --}}
    {{-- ============================================================ --}}
    @if($pendingUsers > 0 || $pendingEnrollments > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5 mb-8">
        @if($pendingUsers > 0)
        <a href="{{ url('/admin/users') }}" class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl shadow-lg shadow-rose-100 p-5 text-white flex items-center gap-4 hover:shadow-xl hover:scale-[1.01] transition-all group">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider opacity-80">Menunggu Akun ACC</p>
                <p class="text-2xl font-black">{{ $pendingUsers }} <span class="text-sm font-bold opacity-80">user</span></p>
            </div>
            <i data-lucide="arrow-right" class="w-5 h-5 ml-auto opacity-50 group-hover:translate-x-1 transition-transform"></i>
        </a>
        @endif

        @if($pendingTickets > 0)
        <a href="{{ url('/admin/tickets') }}" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg shadow-indigo-100 p-5 text-white flex items-center gap-4 hover:shadow-xl hover:scale-[1.01] transition-all group">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="message-square" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider opacity-80">Tiket Bantuan Baru</p>
                <p class="text-2xl font-black">{{ $pendingTickets }} <span class="text-sm font-bold opacity-80">tiket</span></p>
            </div>
            <i data-lucide="arrow-right" class="w-5 h-5 ml-auto opacity-50 group-hover:translate-x-1 transition-transform"></i>
        </a>
        @endif

        @if($pendingEnrollments > 0)
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl shadow-lg shadow-amber-100 p-5 text-white flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider opacity-80">Permintaan Masuk Kelas</p>
                <p class="text-2xl font-black">{{ $pendingEnrollments }} <span class="text-sm font-bold opacity-80">request</span></p>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SECONDARY STATS ROW                                           --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                <i data-lucide="calendar" class="w-4 h-4 text-sky-600"></i>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Pertemuan</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($totalPertemuan) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-pink-50 rounded-lg flex items-center justify-center">
                <i data-lucide="file-pen-line" class="w-4 h-4 text-pink-600"></i>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Ujian</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($totalUjian) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-teal-50 rounded-lg flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4 text-teal-600"></i>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Total User</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($totalMahasiswa + $totalDosen) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-orange-50 rounded-lg flex items-center justify-center">
                <i data-lucide="activity" class="w-4 h-4 text-orange-600"></i>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Registrasi Hari Ini</p>
                <p class="text-lg font-black text-gray-900">{{ \App\Models\User::whereDate('created_at', today())->count() }}</p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CHARTS ROW                                                    --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
        {{-- Registration Trend Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-black text-gray-900 text-lg">Tren Registrasi</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-0.5">12 Bulan Terakhir</p>
                </div>
                <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5 text-indigo-600"></i>
                </div>
            </div>
            <div class="h-64">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>

        {{-- User Role Distribution --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-black text-gray-900 text-lg">Distribusi User</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-0.5">Berdasarkan Role</p>
                </div>
                <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-violet-600"></i>
                </div>
            </div>
            <div class="h-48 flex items-center justify-center">
                <canvas id="roleChart"></canvas>
            </div>
            <div class="flex justify-center gap-4 mt-4">
                @foreach($userRoleDistribution as $dist)
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dist['role'] === 'Admin' ? 'bg-rose-500' : ($dist['role'] === 'Dosen' ? 'bg-emerald-500' : 'bg-indigo-500') }}"></span>
                        <span class="text-[10px] font-bold text-gray-500">{{ $dist['role'] }} ({{ $dist['count'] }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KELAS POPULATION BAR CHART                                    --}}
    {{-- ============================================================ --}}
    @if(count($kelasPopulation) > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-black text-gray-900 text-lg">Populasi Kelas</h3>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-0.5">Top 8 Kelas Berdasarkan Jumlah Mahasiswa</p>
            </div>
            <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-amber-600"></i>
            </div>
        </div>
        <div class="h-64">
            <canvas id="kelasChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- BOTTOM ROW: RECENT ACTIVITY + PENDING                        --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        {{-- Recent Users --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900">User Terbaru</h3>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">5 Pendaftaran Terakhir</p>
                    </div>
                </div>
                <a href="{{ url('/admin/users') }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 transition">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentUsers as $user)
                    <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition-colors">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-white shrink-0
                            {{ $user['role'] === 'admin' ? 'bg-rose-500' : ($user['role'] === 'dosen' ? 'bg-emerald-500' : 'bg-indigo-500') }}">
                            {{ strtoupper(substr($user['name'], 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-gray-900 truncate">{{ $user['name'] }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $user['email'] }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider
                                {{ $user['is_active'] ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                {{ $user['is_active'] ? 'Aktif' : 'Pending' }}
                            </span>
                            <p class="text-[9px] text-gray-400 mt-0.5">{{ $user['created_at'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">Belum ada user terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Approvals --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900">Menunggu ACC</h3>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Akun Belum Disetujui</p>
                    </div>
                </div>
                @if($pendingUsers > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full animate-pulse">{{ $pendingUsers }}</span>
                @endif
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentPendingUsers as $pending)
                    <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs shrink-0">
                            {{ strtoupper(substr($pending['name'], 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-gray-900 truncate">{{ $pending['name'] }}</p>
                            <p class="text-[10px] text-gray-400">{{ $pending['email'] }} · {{ $pending['created_at'] }}</p>
                        </div>
                        <button wire:click="quickApproveUser({{ $pending['id'] }})" class="flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-colors text-[10px] font-black uppercase tracking-wider shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> ACC
                        </button>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto text-emerald-200 mb-2"></i>
                        <p class="text-sm font-bold text-emerald-600">Semua user sudah disetujui! 🎉</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CHART.JS SCRIPTS                                              --}}
    {{-- ============================================================ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Default Chart.js styling
            Chart.defaults.font.family = "'Inter', 'system-ui', 'sans-serif'";
            Chart.defaults.font.weight = 600;

            // Registration Trend Line Chart
            const regCtx = document.getElementById('registrationChart');
            if (regCtx) {
                const regData = @json($registrationTrend);
                new Chart(regCtx, {
                    type: 'line',
                    data: {
                        labels: regData.map(d => d.label),
                        datasets: [{
                            label: 'Registrasi',
                            data: regData.map(d => d.count),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e1b4b',
                                titleFont: { weight: 800 },
                                bodyFont: { weight: 600 },
                                padding: 12,
                                cornerRadius: 12,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: 700 }, color: '#9ca3af' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    font: { size: 10, weight: 700 },
                                    color: '#9ca3af',
                                    stepSize: 1,
                                    callback: function(value) { if (Number.isInteger(value)) return value; }
                                }
                            }
                        }
                    }
                });
            }

            // Role Distribution Doughnut Chart
            const roleCtx = document.getElementById('roleChart');
            if (roleCtx) {
                const roleData = @json($userRoleDistribution);
                new Chart(roleCtx, {
                    type: 'doughnut',
                    data: {
                        labels: roleData.map(d => d.role),
                        datasets: [{
                            data: roleData.map(d => d.count),
                            backgroundColor: ['#f43f5e', '#10b981', '#6366f1'],
                            borderWidth: 0,
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e1b4b',
                                titleFont: { weight: 800 },
                                bodyFont: { weight: 600 },
                                padding: 12,
                                cornerRadius: 12,
                            }
                        }
                    }
                });
            }

            // Kelas Population Bar Chart
            const kelasCtx = document.getElementById('kelasChart');
            if (kelasCtx) {
                const kelasData = @json($kelasPopulation);
                new Chart(kelasCtx, {
                    type: 'bar',
                    data: {
                        labels: kelasData.map(d => d.name + ' (' + d.course + ')'),
                        datasets: [{
                            label: 'Mahasiswa',
                            data: kelasData.map(d => d.count),
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(244, 63, 94, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(14, 165, 233, 0.8)',
                                'rgba(251, 146, 60, 0.8)',
                                'rgba(168, 85, 247, 0.8)',
                            ],
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e1b4b',
                                titleFont: { weight: 800 },
                                bodyFont: { weight: 600 },
                                padding: 12,
                                cornerRadius: 12,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 9, weight: 700 }, color: '#9ca3af', maxRotation: 45 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    font: { size: 10, weight: 700 },
                                    color: '#9ca3af',
                                    stepSize: 1,
                                    callback: function(value) { if (Number.isInteger(value)) return value; }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
