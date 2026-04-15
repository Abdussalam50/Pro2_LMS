<div class="relative max-w-[1600px] mx-auto p-3 sm:p-4 md:p-8 space-y-4 md:space-y-6">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        .dash-root { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 32px rgba(0,0,0,0.06);
            border-radius: 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @media (hover: hover) {
            .glass-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 24px 48px rgba(79,70,229,0.1);
                border-color: rgba(79,70,229,0.15);
            }
        }
        .stat-pill {
            background: rgba(79, 70, 229, 0.06);
            color: #4338ca;
            border: 1px solid rgba(79,70,229,0.1);
        }
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-12px) scale(1.03); }
        }
        .float-anim { animation: float 8s ease-in-out infinite; }
        @keyframes shimmer {
            from { background-position: -200% center; }
            to { background-position: 200% center; }
        }
        .shimmer-text {
            background: linear-gradient(90deg, #fff 0%, #c7d2fe 40%, #fff 60%, #818cf8 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s linear infinite;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <div class="dash-root">
        @php
            $user = auth()->user();
            $hour = (int) date('H');
            $greeting = $hour < 5 ? 'Selamat Dini Hari' : ($hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam')));
            $greetIcon = $hour < 5 ? 'moon' : ($hour < 12 ? 'sunrise' : ($hour < 15 ? 'sun' : ($hour < 19 ? 'sunset' : 'moon')));
        @endphp

        {{-- ======================= HERO SECTION ======================= --}}
        <div class="relative overflow-hidden rounded-[2rem] md:rounded-[2.5rem] hero-gradient p-6 md:p-14 text-white shadow-2xl">
            {{-- Background Orbs --}}
            <div class="floating-orb w-96 h-96 bg-indigo-600/40 -top-20 -left-20 float-anim"></div>
            <div class="floating-orb w-72 h-72 bg-fuchsia-600/30 top-0 right-1/4" style="animation: float 10s ease-in-out 2s infinite;"></div>
            <div class="floating-orb w-64 h-64 bg-violet-500/30 -bottom-16 right-10" style="animation: float 12s ease-in-out 1s infinite;"></div>

            <div class="relative z-10">
                {{-- Top: Greeting --}}
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/8 border border-white/12 backdrop-blur-lg text-indigo-200 text-[10px] font-black uppercase tracking-widest">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            </span>
                            <i data-lucide="{{ $greetIcon }}" class="w-3 h-3"></i>
                            Sesi Aktif
                        </div>
                        <div>
                            <p class="text-indigo-300/70 text-sm font-semibold">{{ $greeting }},</p>
                            <h1 class="text-3xl md:text-6xl font-black tracking-tight leading-tight shimmer-text">
                                {{ $user->name }}
                            </h1>
                        </div>
                        <p class="text-indigo-200/50 text-xs font-medium hidden sm:block">
                            Anda memiliki <span class="text-white font-bold">{{ count($this->todaySchedule) }} kelas</span> untuk dikelola hari ini.
                        </p>
                    </div>
                </div>

                {{-- Stats Grid: 3-col on mobile, horizontal on desktop --}}
                <div class="grid grid-cols-3 md:flex md:flex-row gap-3">
                    <div class="flex flex-col items-center justify-center py-4 px-2 md:px-8 md:py-6 rounded-2xl bg-white/6 border border-white/10 backdrop-blur-xl hover:bg-white/12 transition-all">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-indigo-500/30 flex items-center justify-center mb-2 border border-indigo-400/20">
                            <i data-lucide="users" class="w-4 h-4 md:w-5 md:h-5 text-indigo-200"></i>
                        </div>
                        <span class="text-2xl md:text-3xl font-black text-white leading-none mb-1">{{ $this->stats['mahasiswa'] }}</span>
                        <span class="text-[8px] md:text-[9px] font-black text-indigo-300/60 uppercase tracking-wider">Mahasiswa</span>
                    </div>
                    <div class="flex flex-col items-center justify-center py-4 px-2 md:px-8 md:py-6 rounded-2xl bg-white/6 border border-white/10 backdrop-blur-xl hover:bg-white/12 transition-all">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-fuchsia-500/30 flex items-center justify-center mb-2 border border-fuchsia-400/20">
                            <i data-lucide="file-text" class="w-4 h-4 md:w-5 md:h-5 text-fuchsia-200"></i>
                        </div>
                        <span class="text-2xl md:text-3xl font-black text-white leading-none mb-1">{{ $this->stats['materi'] }}</span>
                        <span class="text-[8px] md:text-[9px] font-black text-indigo-300/60 uppercase tracking-wider">Materi</span>
                    </div>
                    <div class="flex flex-col items-center justify-center py-4 px-2 md:px-8 md:py-6 rounded-2xl bg-white/6 border border-white/10 backdrop-blur-xl hover:bg-white/12 transition-all">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-amber-500/30 flex items-center justify-center mb-2 border border-amber-400/20">
                            <i data-lucide="message-circle" class="w-4 h-4 md:w-5 md:h-5 text-amber-200"></i>
                        </div>
                        <span class="text-2xl md:text-3xl font-black text-white leading-none mb-1">{{ $this->stats['diskusi'] }}</span>
                        <span class="text-[8px] md:text-[9px] font-black text-indigo-300/60 uppercase tracking-wider">Diskusi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= QUICK ACTIONS ROW ======================= --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 my-6">
            <a href="/dosen/classes" class="glass-card p-4 md:p-5 flex items-center gap-3 md:gap-4 group cursor-pointer active:scale-95 transition-all">
                <div class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/30 shrink-0">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 md:w-6 md:h-6 text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Portal</p>
                    <p class="text-xs md:text-sm font-black text-gray-900 truncate">Kelas Saya</p>
                </div>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-gray-300 ml-auto hidden sm:block"></i>
            </a>
            <button wire:click="openUjianModal" class="glass-card p-4 md:p-5 flex items-center gap-3 md:gap-4 group cursor-pointer active:scale-95 transition-all w-full text-left">
                <div class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/30 shrink-0">
                    <i data-lucide="pencil-line" class="w-5 h-5 md:w-6 md:h-6 text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Buat</p>
                    <p class="text-xs md:text-sm font-black text-gray-900 truncate">Ujian Baru</p>
                </div>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-gray-300 ml-auto hidden sm:block"></i>
            </button>
            <button wire:click="setTab('announcements')" class="glass-card p-4 md:p-5 flex items-center gap-3 md:gap-4 group cursor-pointer active:scale-95 transition-all w-full text-left">
                <div class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/30 shrink-0">
                    <i data-lucide="megaphone" class="w-5 h-5 md:w-6 md:h-6 text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Kirim</p>
                    <p class="text-xs md:text-sm font-black text-gray-900 truncate">Pengumuman</p>
                </div>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-gray-300 ml-auto hidden sm:block"></i>
            </button>
            <a href="{{ route('dosen.bank-soal') }}" class="glass-card p-4 md:p-5 flex items-center gap-3 md:gap-4 group cursor-pointer active:scale-95 transition-all">
                <div class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-rose-500 flex items-center justify-center shadow-lg shadow-rose-500/30 shrink-0">
                    <i data-lucide="clock" class="w-5 h-5 md:w-6 md:h-6 text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Deadline</p>
                    <p class="text-xs md:text-sm font-black text-gray-900 truncate">
                        <span class="text-rose-600">{{ count($this->upcomingAssignments) }}</span> Tugas
                    </p>
                </div>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-gray-300 ml-auto hidden sm:block"></i>
            </a>
        </div>

        {{-- ======================= MAIN CONTENT GRID ======================= --}}
        <div class="grid grid-cols-12 gap-6 mb-6">

            {{-- LEFT: Course Browser (8 cols) --}}
            <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

                {{-- Tab Switcher + Course/Announcement Browser --}}
                <div class="glass-card overflow-hidden flex flex-col">
                    <div class="px-8 pt-8 pb-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-900">Katalog Perkuliahan</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Mata Kuliah &amp; Broadcast</p>
                        </div>
                        <div class="flex bg-gray-100/80 p-1.5 rounded-2xl gap-1 self-start md:self-center">
                            <button wire:click="setTab('courses')" class="px-5 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest {{ $activeTab === 'courses' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                                <i data-lucide="book" class="w-3.5 h-3.5 inline mr-1.5 -mt-0.5"></i> Matkul
                            </button>
                            <button wire:click="setTab('announcements')" class="px-5 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest {{ $activeTab === 'announcements' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                                <i data-lucide="megaphone" class="w-3.5 h-3.5 inline mr-1.5 -mt-0.5"></i> Broadcast
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        @if($activeTab === 'courses')
                            @if(count($this->courses) === 0)
                                <div class="flex flex-col items-center justify-center py-24 opacity-25">
                                    <i data-lucide="inbox" class="w-16 h-16 mb-4"></i>
                                    <p class="font-black uppercase tracking-widest text-[11px]">Belum ada mata kuliah</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    @foreach($this->courses as $course)
                                        <div wire:key="course-{{ $course['id'] }}" class="p-6 rounded-[1.5rem] border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-xl hover:shadow-indigo-500/6 hover:border-indigo-100 transition-all group relative cursor-pointer">
                                            <div class="flex justify-between items-start mb-5">
                                                <span class="text-[9px] font-black bg-indigo-600 text-white px-3 py-1.5 rounded-xl uppercase tracking-widest shadow-sm shadow-indigo-600/30">{{ $course['code'] }}</span>
                                                <div class="w-8 h-8 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-indigo-500"></i>
                                                </div>
                                            </div>
                                            <h4 class="font-black text-gray-900 text-base mb-1 leading-tight group-hover:text-indigo-700 transition-colors">{{ $course['name'] }}</h4>
                                            <p class="text-[11px] text-gray-400 font-medium mb-5 line-clamp-2">{{ $course['description'] ?: 'Tidak ada deskripsi.' }}</p>
                                            <div class="flex flex-wrap gap-2 pt-4 border-t border-dashed border-gray-200">
                                                @forelse($course['classes'] as $cls)
                                                    <a href="/dosen/classes/{{ $cls['id'] }}" class="text-[10px] font-black bg-white text-gray-600 px-4 py-1.5 rounded-xl hover:bg-indigo-600 hover:text-white hover:shadow-md hover:shadow-indigo-200 transition-all border border-gray-100 uppercase tracking-tighter">
                                                        {{ $cls['name'] }}
                                                    </a>
                                                @empty
                                                    <span class="text-[10px] text-gray-300 italic font-bold tracking-widest">Vacant</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="space-y-4">
                                @forelse($this->announcements as $ann)
                                    <div wire:key="ann-{{ $ann['id'] }}" class="p-6 rounded-[1.5rem] bg-indigo-50/60 border border-indigo-100/60 hover:bg-white hover:shadow-lg hover:shadow-indigo-50 transition-all group border-l-4 border-l-indigo-500">
                                        <div class="flex items-start gap-4 mb-3">
                                            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100 shrink-0">
                                                <i data-lucide="megaphone" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-gray-900 leading-tight group-hover:text-indigo-700 transition-colors">{{ $ann['title'] }}</h4>
                                                <div class="flex items-center gap-2 text-[9px] text-gray-400 font-black uppercase tracking-wider mt-0.5">
                                                    <span>{{ $ann['created_at'] }}</span>
                                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                    <span class="text-indigo-400">{{ $ann['author_name'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed font-medium pl-14">"{{ $ann['content'] }}"</p>
                                    </div>
                                @empty
                                    <div class="text-center py-24 opacity-25 flex flex-col items-center">
                                        <i data-lucide="cloud-off" class="w-12 h-12 mb-4"></i>
                                        <p class="font-black uppercase tracking-widest text-[11px]">Belum ada pengumuman</p>
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT: Tugas Mendatang (4 cols) — sejajar dengan Katalog --}}
            <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

                {{-- Upcoming Deadlines --}}
                <div class="glass-card p-8 flex flex-col flex-1">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-black text-lg text-gray-900 leading-none">Tugas Mendatang</h3>
                            <p class="text-[9px] text-rose-400 font-black uppercase tracking-widest mt-1">Deadline terdekat</p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500">
                            <i data-lucide="timer" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse($this->upcomingAssignments as $assignment)
                            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-rose-50/60 border border-rose-100/60 hover:bg-white hover:border-rose-200 transition-all group">
                                <div class="w-8 h-8 rounded-xl bg-white border border-rose-100 flex items-center justify-center shrink-0">
                                    <i data-lucide="clipboard-list" class="w-4 h-4 text-rose-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold text-gray-800 truncate">{{ $assignment->master_soal }}</p>
                                    <p class="text-[9px] font-black text-rose-400 uppercase tracking-wider">{{ $assignment->tenggat_waktu->format('d M') }}</p>
                                </div>
                                <i data-lucide="chevron-right" class="w-4 h-4 text-rose-300 group-hover:text-rose-500 transition-colors shrink-0"></i>
                            </div>
                        @empty
                            <div class="flex flex-col items-center text-center py-10 opacity-30">
                                <i data-lucide="check-circle-2" class="w-10 h-10 mb-3 text-emerald-500"></i>
                                <p class="text-[10px] font-black uppercase tracking-widest">Tidak ada deadline dekat</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= GRAFIK NILAI (FULL WIDTH) ======================= --}}
        <div class="glass-card p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h3 class="font-black text-xl text-gray-900 leading-none">Grafik Nilai</h3>
                    <p class="text-[9px] text-indigo-500 font-black uppercase tracking-widest mt-1">Analisis Rata-rata Performa Kelas</p>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto">
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-gray-400 uppercase tracking-widest pl-1">Mata Kuliah</label>
                        <select wire:model.live="selectedMataKuliahId" class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-[11px] font-bold focus:ring-2 focus:ring-indigo-500/20 min-w-[140px]">
                            <option value="all">Semua Mata Kuliah</option>
                            @foreach($this->courses as $course)
                                <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-gray-400 uppercase tracking-widest pl-1">Kelas</label>
                        <select wire:model.live="selectedKelasId" class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-[11px] font-bold focus:ring-2 focus:ring-indigo-500/20 min-w-[140px]">
                            <option value="all">Semua Kelas</option>
                            @foreach($this->kelasList as $kelas)
                                <option value="{{ $kelas->kelas_id }}">{{ $kelas->kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-gray-400 uppercase tracking-widest pl-1">Pertemuan</label>
                        <select wire:model.live="selectedPertemuanId" class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-[11px] font-bold focus:ring-2 focus:ring-indigo-500/20 min-w-[160px]">
                            <option value="all">Semua Pertemuan</option>
                            @foreach($this->pertemuanList as $index => $p)
                                <option value="{{ $p->pertemuan_id }}">Pertemuan {{ $index + 1 }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 self-end">
                        <i data-lucide="line-chart" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>
            <div class="relative w-full" style="height: 300px;" wire:ignore>
                <canvas id="gradeChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let gradeChart;
            const initChart = (data = null) => {
                const ctx = document.getElementById('gradeChart');
                if (!ctx) return;
                
                if (gradeChart) {
                    console.log('[ChartJS] Destroying old chart instance');
                    gradeChart.destroy();
                }

                const chartData = data || @json($this->gradeChartData);
                console.log('[ChartJS] Initializing chart with data:', chartData);
                
                gradeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Rata-rata Nilai',
                            data: chartData.data,
                            backgroundColor: 'rgba(99, 102, 241, 0.8)',
                            borderRadius: 10,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(79, 70, 229, 1)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e1b4b',
                                titleFont: { size: 12, weight: 'bold', family: 'Inter' },
                                bodyFont: { size: 12, family: 'Inter' },
                                padding: 12,
                                cornerRadius: 12,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { color: 'rgba(0,0,0,0.04)' },
                                border: { display: false },
                                ticks: { font: { size: 10, weight: 'bold', family: 'Inter' }, color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { font: { size: 10, weight: 'bold', family: 'Inter' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            };

            initChart();

            Livewire.on('chartUpdated', (eventData) => {
                console.log('[Livewire] chartUpdated event received payload:', eventData);
                const freshData = Array.isArray(eventData) ? eventData[0] : eventData;
                
                if (freshData && freshData.labels && freshData.data) {
                    console.log('[Livewire] Updating chart with fresh data object');
                    initChart(freshData);
                } else {
                    console.warn('[Livewire] chartUpdated payload is invalid or empty', freshData);
                }
            });
        });
    </script>
    <!-- Ujian Modal -->
    @if($showUjianModal)
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white/90 backdrop-blur-xl p-8 rounded-[1.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-2xl border border-white/40 overflow-y-auto max-h-[90vh]">
                <div class="flex items-center gap-4 mb-8 text-black">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $ujianForm['ujian_id'] ? 'Edit Ujian' : 'Tambah Ujian Baru' }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Konfigurasi parameter ujian mahasiswa</p>
                    </div>
                </div>

                <form wire:submit.prevent="saveUjian" class="space-y-6 text-black">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Mata Kuliah</label>
                            <select wire:model="ujianForm.mata_kuliah_id" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                <option value="">-- Pilih Matkul --</option>
                                @foreach($this->courses as $course)
                                    <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kelas</label>
                            <select wire:model="ujianForm.kelas_id" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($this->courses as $course)
                                    @foreach($course['classes'] as $cls)
                                        <option value="{{ $cls['id'] }}">{{ $cls['name'] }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Ujian</label>
                        <input type="text" wire:model="ujianForm.nama_ujian" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Contoh: UTS Aljabar Linear">
                        @error('ujianForm.nama_ujian') <span class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi & Instruksi</label>
                        <textarea wire:model="ujianForm.deskripsi" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-24" placeholder="Tulis instruksi pengerjaan..."></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jenis</label>
                            <select wire:model="ujianForm.jenis_ujian" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                                <option value="uts">UTS</option>
                                <option value="uas">UAS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jumlah Soal</label>
                            <input type="number" wire:model="ujianForm.jumlah_soal" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot (%)</label>
                            <input type="number" wire:model="ujianForm.bobot_nilai" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu Mulai</label>
                            <input type="datetime-local" wire:model="ujianForm.waktu_mulai" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu Selesai</label>
                            <input type="datetime-local" wire:model="ujianForm.waktu_selesai" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50/50 rounded-[2rem] border border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="ujianForm.is_active" class="peer h-5 w-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Aktif</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="ujianForm.is_open" class="peer h-5 w-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Buka Akses</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Mode Batasan Ujian</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-gray-50 {{ $ujianForm['mode_batasan'] === 'open' ? 'border-indigo-600 bg-indigo-50/30' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="open" class="peer sr-only">
                                <span class="text-xs font-black uppercase tracking-widest">Terbuka</span>
                            </label>

                            <label class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-amber-50 {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'border-amber-500 bg-amber-50/50' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="materi_only" class="peer sr-only">
                                <span class="text-xs font-black uppercase tracking-widest">Buka Materi</span>
                            </label>

                            <label class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-rose-50 {{ $ujianForm['mode_batasan'] === 'strict' ? 'border-rose-500 bg-rose-50/50' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="strict" class="peer sr-only">
                                <span class="text-xs font-black uppercase tracking-widest">Ketat (Lock)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 sticky bottom-0 bg-white/80 backdrop-blur-md pb-4">
                        <button type="button" wire:click="$set('showUjianModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit" class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            {{ $ujianForm['ujian_id'] ? 'Update Ujian' : 'Simpan Ujian' }}
                            <i data-lucide="save" class="w-3.5 h-3.5 transition-transform group-hover:scale-110"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
