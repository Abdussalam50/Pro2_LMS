<div>
    <!-- Header -->
    <div class="mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        @if($this->isReadonly)
            <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 p-4 rounded-xl text-amber-800 shadow-sm animate-pulse">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <i data-lucide="archive" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider">Arsip / Read-Only</h4>
                    <p class="text-xs opacity-80 font-medium">Kelas ini berasal dari periode akademik yang sudah tidak aktif ({{ $classData['academic_period']['name'] ?? 'Lama' }}). Anda tidak dapat melakukan absensi atau mengerjakan tugas baru.</p>
                </div>
            </div>
        @endif
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <a href="/mahasiswa/classes" class="text-sm border border-gray-300 text-gray-500 hover:bg-gray-50 px-3 py-1.5 rounded-lg inline-flex items-center gap-2 transition mb-4">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Kelas
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-bold text-xl font-mono">
                        {{ substr($classData['code'], 0, 2) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">{{ $classData['name'] }}</h1>
                        <p class="text-gray-500 mt-1 font-medium">{{ $classData['course_name'] }} ({{ $classData['course_code'] }}) • Dosen: {{ $classData['lecturer_name'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-xl flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">Presensi</div>
                        <div class="text-lg font-black text-indigo-900">{{ $attendanceRate }}%</div>
                    </div>
                </div>

                <div class="bg-purple-50 border border-purple-100 p-3 rounded-xl flex items-center gap-3">
                    <div class="bg-purple-600 p-2 rounded-lg text-white">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-purple-600 font-bold uppercase tracking-wider">Tugas Selesai</div>
                        <div class="text-lg font-black text-purple-900">{{ $assignmentProgress }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex space-x-4 mb-6 border-b border-gray-200 pb-1 overflow-x-auto text-black">
        <button wire:click="setTab('materials')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'materials' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="book-open" class="w-4 h-4"></i> Materi & Pertemuan
            @if($activeTab === 'materials') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('assignments')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'assignments' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="file-check-2" class="w-4 h-4"></i> Tugas
            @if($activeTab === 'assignments') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('attendance')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'attendance' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="clipboard-check" class="w-4 h-4"></i> Presensi
            @if($activeTab === 'attendance') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('diskusi')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'diskusi' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="message-circle" class="w-4 h-4"></i> Diskusi
            @if($activeTab === 'diskusi') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('rekapitulasi')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'rekapitulasi' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Rekapitulasi
            @if($activeTab === 'rekapitulasi') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content Area -->
        <div class="lg:col-span-2">
            @if($activeTab === 'materials')
                <div class="space-y-4">
                    @foreach($meetings as $meeting)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div @click="open = !open; if(!open) $wire.setActiveMeeting('{{ $meeting['id'] }}')" class="p-5 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="bg-indigo-100 p-2.5 rounded-lg text-indigo-600">
                                        <i data-lucide="calendar" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800">{{ $meeting['title'] }}</h3>
                                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                            <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $meeting['date'] }}</span>
                                            @if($meeting['learning_model'] !== 'none')
                                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase text-xs font-bold tracking-wide">
                                                    {{ $meeting['learning_model'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                            </div>

                            <div x-show="open" x-collapse x-cloak class="p-6 border-t border-gray-100 bg-gray-50">
                                <!-- Learning Syntax For Students -->
                                @if($meeting['learning_model'] !== 'none')
                                    @livewire('mahasiswa.learning-flow', [
                                        'kelasId'    => $classId,
                                        'pertemuanId'=> $meeting['id'],
                                        'embedded'   => true,
                                    ], 'flow-'.$meeting['id'])
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($activeTab === 'assignments')
                <div class="space-y-4">
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-gray-800">Daftar Semua Tugas</h2>
                            <p class="text-sm text-gray-500">Kelola dan kerjakan tugas Anda di sini.</p>
                        </div>
                        <div class="bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Total Tugas</span>
                            <div class="text-2xl font-black text-indigo-900">{{ count($rekapData['assignments']['list']) }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        @forelse($rekapData['assignments']['list'] as $ass)
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 transition group flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 {{ $ass['submitted'] ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }} rounded-xl flex items-center justify-center">
                                        <i data-lucide="{{ $ass['submitted'] ? 'check-circle' : 'file-edit' }}" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $ass['title'] }}</h3>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3"></i> Tenggat: {{ $ass['deadline'] }}
                                            </span>
                                            @if($ass['submitted'])
                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">
                                                    {{ $ass['status'] }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 text-[10px] font-bold uppercase tracking-wider">
                                                    Belum Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 w-full sm:w-auto">
                                    @if($ass['is_graded'])
                                        <div class="text-right mr-2">
                                            <div class="text-lg font-black text-emerald-600">{{ $ass['score'] }}</div>
                                            <div class="text-[8px] text-gray-400 font-bold uppercase -mt-1">Nilai</div>
                                        </div>
                                    @endif
                                    <a href="/mahasiswa/soal/{{ $ass['id'] }}" 
                                       class="flex-1 sm:flex-initial text-center {{ $this->isReadonly && !$ass['submitted'] ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100' }} text-white px-6 py-2.5 rounded-xl font-bold text-sm transition shadow-lg">
                                        @if($this->isReadonly && !$ass['submitted'])
                                            <i data-lucide="lock" class="w-3.5 h-3.5 inline mr-1"></i> Terkunci
                                        @else
                                            {{ $ass['submitted'] ? 'Buka Review' : 'Kerjakan Sekarang' }}
                                        @endif
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white p-12 rounded-2xl border border-gray-200 border-dashed text-center">
                                <i data-lucide="clipboard-list" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                                <p class="text-gray-400 font-medium">Belum ada tugas yang tersedia di kelas ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if($activeTab === 'diskusi')
                <div class="h-[600px] rounded-2xl border border-gray-200 overflow-hidden">
                    @livewire('discussion-hub', ['kelasId' => $classId])
                </div>
            @endif

            @if($activeTab === 'rekapitulasi')
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <!-- Final Grade Hero Card -->
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
                        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-2xl"></div>
                        
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                            <div>
                                <h3 class="text-indigo-100 text-xs font-black uppercase tracking-[0.3em] mb-3">Estimasi Nilai Akhir</h3>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-7xl font-black tracking-tighter">{{ number_format($finalGrade, 1) }}</span>
                                    <span class="text-2xl font-bold text-indigo-200">/ 100</span>
                                </div>
                                <p class="text-indigo-100/60 text-[10px] font-bold uppercase tracking-widest mt-4 flex items-center gap-2">
                                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                    Dihitung berdasarkan rata-rata tertimbang kategori
                                </p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20 flex flex-col items-center min-w-[160px]">
                                <div class="text-[10px] font-black uppercase tracking-widest text-indigo-100 mb-2">Predikat</div>
                                <div class="text-5xl font-black text-white">
                                    @if($finalGrade >= 85) A @elseif($finalGrade >= 75) B @elseif($finalGrade >= 65) C @elseif($finalGrade >= 50) D @else E @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach(['presensi', 'tugas', 'kuis', 'uts', 'uas', 'lainnya'] as $cat)
                            @php 
                                $score = $categoryScores[$cat] ?? 0;
                                $weight = $gradingWeights[$cat] ?? 0;
                            @endphp
                            @if($weight > 0)
                                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                                <i data-lucide="{{ $cat === 'presensi' ? 'user-check' : ($cat === 'tugas' ? 'clipboard-list' : ($cat === 'kuis' ? 'help-circle' : ($cat === 'uts' ? 'award' : ($cat === 'uas' ? 'graduation-cap' : 'box')))) }}" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">{{ $cat }}</h4>
                                                <p class="text-[10px] font-bold text-indigo-600">Bobot: {{ $weight }}%</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-2xl font-black text-gray-900">{{ number_format($score, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-50 h-2 rounded-full overflow-hidden">
                                        <div class="bg-indigo-600 h-full transition-all duration-1000" style="width: {{ $score }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="p-6 bg-amber-50 border border-amber-100 rounded-2xl flex items-start gap-4">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-amber-500 shrink-0"></i>
                        <p class="text-xs text-amber-800 font-medium leading-relaxed italic">
                            Nilai di atas adalah rekapan sementara. Nilai akhir resmi akan divalidasi oleh dosen pengampu di akhir semester setelah seluruh komponen penilaian terpenuhi.
                        </p>
                    </div>
                </div>
            @endif

            @if($activeTab === 'attendance')
                <div class="max-w-md mx-auto">
                    <div class="bg-white p-8 rounded-3xl shadow-xl shadow-indigo-100 border border-indigo-50 relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full opacity-50"></div>
                        
                        <div class="relative z-10 text-center">
                            <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-6 shadow-lg shadow-indigo-200">
                                <i data-lucide="user-check" class="w-8 h-8"></i>
                            </div>
                            
                            <h2 class="text-2xl font-black text-gray-800 mb-2">Presensi Kehadiran</h2>
                            
                            @if($activePresensi)
                                <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl mb-8">
                                    <div class="flex items-center justify-center gap-2 text-emerald-600 font-bold text-xs uppercase tracking-widest mb-1">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                                        Sesi Presensi Aktif
                                    </div>
                                    <p class="text-emerald-900 font-black text-sm">Pertemuan {{ $activePresensi->pertemuan->pertemuan }}</p>
                                    <p class="text-emerald-600/70 text-[10px] font-bold mt-1">Berakhir: {{ \Carbon\Carbon::parse($activePresensi->expires_at)->format('H:i') }}</p>
                                </div>

                                <p class="text-gray-500 text-sm mb-6 leading-relaxed">Masukkan 6 digit kode yang ditampilkan di layar Dosen atau scan QR Code yang tersedia.</p>
                                
                                @if($attendanceStatus === 'success')
                                    <div class="mb-6 bg-emerald-500 text-white p-4 rounded-xl flex items-center justify-center gap-3 animate-bounce">
                                        <i data-lucide="party-popper" class="w-6 h-6"></i>
                                        <span class="font-black uppercase tracking-widest text-xs">Kehadiran Berhasil!</span>
                                    </div>
                                @elseif($attendanceStatus === 'error')
                                    <div class="mb-6 bg-red-50 border border-red-200 text-red-600 p-4 rounded-xl flex items-center justify-center gap-2">
                                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider">Kode Salah atau Kadaluarsa</span>
                                    </div>
                                @endif

                                @if(!$this->isReadonly)
                                    <div class="space-y-4">
                                        <div class="relative">
                                            <input type="text" 
                                                   wire:model="attendanceCode" 
                                                   maxlength="6"
                                                   placeholder="KODE 6 DIGIT" 
                                                   class="w-full bg-gray-50 border-2 border-gray-100 focus:border-indigo-600 focus:ring-0 rounded-2xl p-4 text-center text-2xl font-black tracking-[0.5em] text-gray-800 transition uppercase placeholder:text-gray-300 placeholder:tracking-normal placeholder:font-bold placeholder:text-sm">
                                        </div>
                                        
                                        <button wire:click="submitAttendance" 
                                                class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl hover:bg-indigo-700 transition active:scale-95 shadow-lg shadow-indigo-100 uppercase tracking-widest text-sm flex items-center justify-center gap-2 group">
                                            <span>Konfirmasi Kehadiran</span>
                                            <i data-lucide="send" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="bg-amber-50 border border-amber-100 p-6 rounded-2xl text-center">
                                        <i data-lucide="lock" class="w-10 h-10 text-amber-300 mx-auto mb-3"></i>
                                        <p class="text-amber-800 font-bold text-sm">Absensi Terkunci</p>
                                        <p class="text-[10px] text-amber-600 mt-1 uppercase tracking-wider">Periode ini sudah diarsipkan</p>
                                    </div>
                                @endif
                            @else
                                <div class="bg-gray-50 border border-gray-100 p-8 rounded-2xl text-center">
                                    <i data-lucide="clock-4" class="w-12 h-12 text-gray-300 mx-auto mb-4 opacity-50"></i>
                                    <p class="text-gray-400 font-bold text-sm">Belum ada sesi presensi aktif.</p>
                                    <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-wider">Tunggu instruksi dari Dosen pengampu</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-8 flex items-center justify-center gap-8">
                        <div class="flex flex-col items-center gap-2 opacity-30 grayscale hover:grayscale-0 hover:opacity-100 transition duration-500 cursor-default">
                            <i data-lucide="smartphone" class="w-6 h-6"></i>
                            <span class="text-[8px] font-black uppercase tracking-tighter">Mobile Ready</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 opacity-30 grayscale hover:grayscale-0 hover:opacity-100 transition duration-500 cursor-default">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                            <span class="text-[8px] font-black uppercase tracking-tighter">Secure Auth</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 opacity-30 grayscale hover:grayscale-0 hover:opacity-100 transition duration-500 cursor-default">
                            <i data-lucide="zap" class="w-6 h-6"></i>
                            <span class="text-[8px] font-black uppercase tracking-tighter">Real-time Sync</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Widget: Live Class Chat -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[600px] sticky top-8">
                @if(!empty($meetings))
                    @livewire('discussion-hub', [
                        'kelasId' => $classId, 
                        'pertemuanId' => $activeMeetingId ?? end($meetings)['id'],
                        'compact' => true
                    ], 'sidebar-hub-'.($activeMeetingId ?? end($meetings)['id']))
                @else
                    <div class="p-8 text-center text-gray-400">
                        <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p class="text-sm">Belum ada pertemuan aktif untuk diskusi kelas.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
