<div>
    <!-- Header -->
    <div class="mb-4 md:mb-8 bg-white p-4 md:p-6 rounded-xl shadow-sm border border-gray-200">
        @if($this->isReadonly)
            <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 p-4 rounded-xl text-amber-800 shadow-sm animate-pulse">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <i data-lucide="archive" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider">Arsip / Read-Only</h4>
                    <p class="text-xs opacity-80 font-medium">Kelas ini berasal dari periode akademik yang sudah tidak aktif ({{ $classData['academic_period']['name'] ?? 'Lama' }}). Anda tidak dapat mengubah data.</p>
                </div>
            </div>
        @endif

        <!-- Top Action Row -->
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <a href="/dosen/dashboard" class="text-xs md:text-sm border border-gray-300 text-gray-500 hover:bg-gray-50 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 md:w-4 md:h-4"></i> Kembali<span class="hidden sm:inline"> ke Daftar Kelas</span>
            </a>
            
            <!-- Mobile Student Count -->
            <div class="md:hidden flex items-center gap-1.5 bg-indigo-50 px-3 py-1.5 rounded-lg text-indigo-700 font-mono font-bold transition">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span class="text-xs">{{ $classData['students_count'] }} Mhs</span>
            </div>
        </div>

        <!-- Class Info Section -->
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div class="flex items-start md:items-center gap-3 md:gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-bold text-xl md:text-2xl font-mono shrink-0">
                    {{ substr($classData['code'], 0, 2) }}
                </div>
                <div>
                    <h1 class="text-xl md:text-3xl font-bold text-gray-800 tracking-tight flex flex-wrap items-center gap-2 mb-1">
                        {{ $classData['name'] }} 
                        <span class="text-indigo-600 font-mono text-xs md:text-xl bg-indigo-50 px-2.5 py-0.5 md:px-3 md:py-1 rounded-md md:rounded-lg border border-indigo-100">{{ $classData['code'] }}</span>
                    </h1>
                    <p class="text-xs md:text-sm text-gray-500 font-medium">{{ $classData['course_name'] }} <span class="block md:inline mt-0.5 md:mt-0">({{ $classData['course_code'] }})</span></p>
                </div>
            </div>
            
            <!-- Desktop Student Count -->
            <div class="hidden md:flex shrink-0 items-center gap-2 bg-indigo-50 px-4 py-2.5 rounded-lg text-indigo-700 font-mono font-bold border border-indigo-100 transition cursor-pointer hover:bg-indigo-100">
                <i data-lucide="users" class="w-5 h-5"></i>
                {{ $classData['students_count'] }} Mahasiswa
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex space-x-4 mb-6 border-b border-gray-200 pb-1 overflow-x-auto text-black">
        <button wire:click="setTab('meetings')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'meetings' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="calendar" class="w-4 h-4"></i> Pertemuan
            @if($activeTab === 'meetings') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('students')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'students' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="users" class="w-4 h-4"></i> Mahasiswa
            @if($activeTab === 'students') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('grades')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'grades' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Nilai
            @if($activeTab === 'grades') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('diskusi')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'diskusi' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="message-square" class="w-4 h-4"></i> Diskusi
            @if($activeTab === 'diskusi') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('materi')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'materi' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="folder-open" class="w-4 h-4"></i> Materi Kelas
            @if($activeTab === 'materi') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        @php
            $ujianListUrl = '/dosen/ujians?' . http_build_query(['kelas_id' => $classId]);
        @endphp
        <button wire:click="setTab('ujian')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'ujian' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="file-pen-line" class="w-4 h-4"></i> Ujian
            @if($activeTab === 'ujian') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
        <button wire:click="setTab('bobot')" class="flex items-center gap-2 px-4 py-2 font-medium transition-all relative whitespace-nowrap {{ $activeTab === 'bobot' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <i data-lucide="settings-2" class="w-4 h-4"></i> Bobot Nilai
            @if($activeTab === 'bobot') <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div> @endif
        </button>
    </div>

    <!-- Active Tab Content: Meetings -->
    @if($activeTab === 'meetings')
        <div class="space-y-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Daftar Pertemuan</h2>
                @if(!$this->isReadonly)
                    <button wire:click="openMeetingModal" class="bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-indigo-700 shadow-sm active:scale-95 transition">
                        <i data-lucide="plus" class="w-5 h-5"></i> Tambah Pertemuan
                    </button>
                @endif
            </div>

            @foreach($meetings as $meeting)
                <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Card Header (Toggle) -->
                    <div @click="open = !open" class="p-5 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition">
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

                    <!-- Expanded Content -->
                    <div x-show="open" x-collapse x-cloak class="p-6 border-t border-gray-100 bg-gray-50 z-10 relative">
                        <div class="space-y-6">
                            <!-- Action Buttons Row -->
                            <div class="grid grid-cols-2 md:flex md:flex-wrap md:justify-end gap-2 mb-6">
                                @if(!$this->isReadonly)
                                    @if($meeting['learning_model'] !== 'none' && isset($meeting['syntax']) && !empty($meeting['syntax']['id']))
                                        <button wire:click="openDuplicateModal('{{ $meeting['syntax']['id'] }}')" class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium hover:bg-indigo-100 flex justify-center items-center gap-1.5 shadow-sm transition">
                                            <i data-lucide="copy" class="w-3.5 h-3.5 md:w-4 md:h-4"></i> Duplikasi<span class="hidden md:inline"> Sintaks</span>
                                        </button>
                                    @endif
                                @endif


                                <a href="/dosen/classes/{{ $classId }}/flow-builder/{{ $meeting['id'] }}" class="{{ $this->isReadonly ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white hover:shadow-lg' }} px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-bold flex justify-center items-center gap-1.5 shadow-sm transition hover:-translate-y-0.5">
                                    <i data-lucide="{{ $this->isReadonly ? 'eye' : 'route' }}" class="w-3.5 h-3.5 md:w-4 md:h-4 {{ $this->isReadonly ? 'text-gray-500' : '' }}"></i> 
                                    @if($this->isReadonly)
                                        Lihat<span class="hidden md:inline"> Alur</span>
                                    @else
                                        Builder<span class="hidden md:inline"> Alur</span>
                                    @endif
                                </a>

                                @if(!$this->isReadonly)
                                    <button wire:click="openMeetingModal('{{ $meeting['id'] }}')" class="bg-white border border-gray-300 text-gray-700 px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium hover:bg-gray-50 flex justify-center items-center gap-1.5 shadow-sm transition">
                                        <i data-lucide="edit" class="w-3.5 h-3.5 md:w-4 md:h-4 text-indigo-600"></i> Edit<span class="hidden md:inline"> Konfigurasi</span>
                                    </button>
                                    <button onclick="confirmDeleteMeeting('{{ $meeting['id'] }}', '{{ $meeting['title'] }}')" class="bg-white border border-red-200 text-red-600 px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium hover:bg-red-50 flex justify-center items-center gap-1.5 shadow-sm transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 md:w-4 md:h-4"></i> Hapus
                                    </button>
                                @endif
                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Materials -->
                                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="font-bold text-gray-800 flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-blue-500"></i> Materi Pembelajaran</h4>
                                        @if(!$this->isReadonly)
                                            <button class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-md transition"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                        @endif
                                    </div>
                                    @if(empty($meeting['materials']))
                                        <p class="text-gray-400 text-sm italic">Belum ada materi.</p>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($meeting['materials'] as $mat)
                                                <li x-data="{ open: false }" class="rounded-xl border border-gray-100 overflow-hidden transition-all" :class="open ? 'border-blue-200 shadow-sm' : 'hover:border-gray-200'">
                                                    <button @click="open = !open" class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-blue-50/50 transition-colors text-left" :class="open ? 'bg-blue-50' : ''">
                                                        <div class="flex items-center gap-2">
                                                            <i data-lucide="file-text" class="w-4 h-4 text-blue-500 shrink-0"></i>
                                                            <span class="text-sm font-bold text-gray-700" :class="open ? 'text-blue-700' : ''">{{ $mat['title'] }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                                            <span class="text-[9px] font-black uppercase tracking-wider" :class="open ? 'text-blue-500' : 'text-gray-400'" x-text="open ? 'Tutup' : 'Lihat Isi'"></span>
                                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180 text-blue-500' : ''"></i>
                                                        </div>
                                                    </button>
                                                    <div x-show="open" x-collapse x-cloak class="border-t border-blue-100">
                                                        <div class="p-4 prose prose-sm max-w-none text-gray-700 bg-white leading-relaxed">
                                                            {!! $mat['content'] !!}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Assignments -->
                                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="font-bold text-gray-800 flex items-center gap-2"><i data-lucide="file-check-2" class="w-4 h-4 text-purple-500"></i> Tugas</h4>
                                        @if(!$this->isReadonly)
                                            <button class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-md transition"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                        @endif
                                    </div>
                                    @if(empty($meeting['assignments']))
                                        <p class="text-gray-400 text-sm italic">Belum ada tugas.</p>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($meeting['assignments'] as $ass)
                                                <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg border border-gray-100 hover:border-gray-200 transition">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-700">{{ $ass['title'] }}</span>
                                                        <span class="text-xs text-red-500 mt-0.5">Tenggat: {{ $ass['tenggat_waktu'] }}</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>

                            <!-- Learning Syntax Preview -->
                            @if($meeting['learning_model'] !== 'none' && !empty($meeting['syntax']['steps']))
                                <div class="mt-6 bg-white p-5 rounded-xl border border-indigo-100 shadow-sm overflow-x-auto">
                                    <h4 class="font-bold text-indigo-900 mb-4 flex items-center gap-2">
                                        <i data-lucide="git-merge" class="w-5 h-5 text-indigo-600"></i> Sintaks Pembelajaran ({{ strtoupper($meeting['learning_model']) }})
                                    </h4>
                                    <div class="flex gap-4 min-w-max pb-2 ">
                                        @foreach($meeting['syntax']['steps'] as $index => $step)
                                            <div class="w-64 flex-shrink-0 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="bg-indigo-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                                                    <h5 class="font-bold text-indigo-900 text-sm truncate" title="{{ $step['title'] }}">{{ $step['title'] }}</h5>
                                                </div>
                                                <ul class="space-y-1 mt-2">
                                                    @foreach($step['sub_steps'] as $sub)
                                                        <li class="text-xs text-indigo-800 flex items-start gap-1">
                                                            <i data-lucide="chevron-right" class="w-3 h-3 mt-0.5 text-indigo-400 flex-shrink-0"></i> 
                                                            <span>{{ $sub }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @if(!empty($step['tools']))
                                                    <div class="mt-2 pt-2 border-t border-indigo-200 flex gap-1">
                                                        @foreach($step['tools'] as $tool)
                                                            <span class="text-[10px] bg-indigo-200 text-indigo-800 px-1.5 py-0.5 rounded font-medium">{{ $tool }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    @if($activeTab === 'students')
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

            {{-- Enrollment Requests Section --}}
            @if(count($pendingRequests) > 0)
                <div class="bg-amber-50 rounded-3xl border border-amber-100 overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-amber-100 flex justify-between items-center bg-amber-100/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white shadow-md shadow-amber-200">
                                <i data-lucide="user-plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-amber-900 uppercase tracking-widest">Permintaan Bergabung</h3>
                                <p class="text-[10px] text-amber-700 font-bold uppercase tracking-widest opacity-70">Menunggu Persetujuan Anda</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-amber-200 text-amber-800 rounded-full text-[10px] font-black uppercase">{{ count($pendingRequests) }} Mahasiswa</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-amber-100/20">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase text-amber-600 tracking-widest">Mahasiswa</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase text-amber-600 tracking-widest">Waktu</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase text-amber-600 tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100">
                                @foreach($pendingRequests as $req)
                                    <tr class="hover:bg-amber-100/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-black text-amber-900">{{ $req['nama'] }}</div>
                                            <div class="text-[10px] text-amber-600 font-bold">{{ $req['nim'] }} • {{ $req['program_studi'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs font-medium text-amber-700">
                                            {{ \Carbon\Carbon::parse($req['created_at'])->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button wire:click="rejectEnrollment('{{ $req['id'] }}')" class="p-2 bg-white text-rose-500 rounded-xl hover:bg-rose-50 border border-rose-100 transition shadow-sm">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                                <button wire:click="approveEnrollment('{{ $req['id'] }}')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-100 flex items-center gap-2 text-xs font-black uppercase tracking-widest">
                                                    <i data-lucide="check" class="w-4 h-4"></i> Setujui
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Mahasiswa</div>
                    <div class="text-3xl font-black text-gray-900">{{ count($mahasiswaList) }}</div>
                    <div class="text-[10px] text-indigo-500 font-bold mt-1">Terdaftar di kelas ini</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mahasiswa Aktif</div>
                    <div class="text-3xl font-black text-emerald-600">{{ collect($mahasiswaList)->where('is_active', true)->count() }}</div>
                    <div class="text-[10px] text-emerald-500 font-bold mt-1">Status akun aktif</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Rata-rata Kehadiran</div>
                    @php
                        $statsWithData = collect($mahasiswaList)->filter(fn($m) => isset($m['stats']));
                        $avgAttendance = $statsWithData->avg(fn($m) => $m['stats']['attendance_pct'] ?? 0);
                    @endphp
                    <div class="text-3xl font-black {{ $avgAttendance >= 75 ? 'text-emerald-600' : ($avgAttendance >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                        {{ $statsWithData->count() > 0 ? round($avgAttendance) . '%' : '-' }}
                    </div>
                    <div class="text-[10px] text-gray-400 font-bold mt-1">Seluruh kelas</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Permintaan Masuk</div>
                    <div class="text-3xl font-black text-amber-500">{{ count($pendingRequests) }}</div>
                    <div class="text-[10px] text-amber-400 font-bold mt-1">Menunggu persetujuan</div>
                </div>
            </div>

            {{-- Main Student Table --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-gray-50 to-white">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight">Daftar Mahasiswa Aktif</h2>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Total: {{ count($mahasiswaList) }} Mahasiswa terdaftar</p>
                    </div>
                    <button wire:click="loadStudentsWithStats" class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 bg-white text-gray-500 hover:text-indigo-600 hover:border-indigo-300 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh Statistik
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[800px]">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-100">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 w-64">Mahasiswa</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Kehadiran</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Progres Tugas</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Nilai Ujian</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Terakhir Aktif</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($mahasiswaList as $m)
                                @php
                                    $stats = $m['stats'] ?? null;
                                    $attendPct = $stats['attendance_pct'] ?? 0;
                                    $tasksSubmitted = $stats['tasks_submitted'] ?? 0;
                                    $tasksTotal = $stats['tasks_total'] ?? 0;
                                    $avgUjian = $stats['avg_ujian'] ?? null;
                                    $lastActivity = $stats['last_activity'] ?? null;

                                    $attendColor = $attendPct >= 75 ? 'bg-emerald-500' : ($attendPct >= 50 ? 'bg-amber-400' : 'bg-rose-400');
                                    $attendTextColor = $attendPct >= 75 ? 'text-emerald-700' : ($attendPct >= 50 ? 'text-amber-600' : 'text-rose-600');
                                    $gradeColor = $avgUjian === null ? 'bg-gray-100 text-gray-400' : ($avgUjian >= 80 ? 'bg-emerald-100 text-emerald-700' : ($avgUjian >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'));
                                    $taskPct = $tasksTotal > 0 ? round(($tasksSubmitted / $tasksTotal) * 100) : 0;
                                    $taskColor = $taskPct >= 75 ? 'bg-emerald-500' : ($taskPct >= 40 ? 'bg-amber-400' : 'bg-rose-400');
                                    $initials = strtoupper(substr($m['name'] ?? 'M', 0, 1) . substr(strstr(($m['name'] ?? 'M'), ' ') ?: 'X', 1, 1));
                                @endphp
                                <tr class="hover:bg-indigo-50/20 transition-all duration-200 group">

                                    {{-- Identitas --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center text-indigo-700 font-black text-sm border border-indigo-100 group-hover:from-indigo-600 group-hover:to-indigo-700 group-hover:text-white group-hover:border-indigo-500 transition-all flex-shrink-0">
                                                {{ $initials }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-black text-gray-900 group-hover:text-indigo-700 transition-colors truncate">{{ $m['name'] }}</div>
                                                <div class="text-[10px] text-gray-400 font-bold font-mono">{{ $m['nim'] ?? '-' }}</div>
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ ($m['is_active'] ?? true) ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500' }} text-[8px] font-black uppercase tracking-widest">
                                                        <span class="w-1 h-1 rounded-full {{ ($m['is_active'] ?? true) ? 'bg-emerald-500' : 'bg-rose-400' }}"></span>
                                                        {{ ($m['is_active'] ?? true) ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kehadiran --}}
                                    <td class="px-6 py-5">
                                        @if($stats)
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[10px] font-black {{ $attendTextColor }}">{{ $attendPct }}%</span>
                                                    <span class="text-[9px] text-gray-400 font-bold">{{ $stats['attended'] }}/{{ $stats['total_meetings'] . ' Ptm' }}</span>
                                                </div>
                                                <div class="w-28 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                    <div class="{{ $attendColor }} h-full rounded-full transition-all" style="width: {{ $attendPct }}%"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-300 font-bold">—</span>
                                        @endif
                                    </td>

                                    {{-- Progres Tugas --}}
                                    <td class="px-6 py-5">
                                        @if($stats)
                                            <div class="space-y-1.5">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[10px] font-black text-gray-600">{{ $tasksSubmitted }}/{{ $tasksTotal }}</span>
                                                    <span class="text-[9px] text-gray-400 font-bold">{{ $taskPct }}%</span>
                                                </div>
                                                @if($tasksTotal > 0)
                                                    <div class="w-28 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                        <div class="{{ $taskColor }} h-full rounded-full transition-all" style="width: {{ $taskPct }}%"></div>
                                                    </div>
                                                @else
                                                    <div class="text-[9px] text-gray-300 italic">Belum ada tugas</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-300 font-bold">—</span>
                                        @endif
                                    </td>

                                    {{-- Nilai Ujian --}}
                                    <td class="px-6 py-5 text-center">
                                        @if($stats)
                                            <span class="inline-block px-3 py-1.5 rounded-xl text-sm font-black {{ $gradeColor }}">
                                                {{ $avgUjian !== null ? $avgUjian : '—' }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-300 font-bold">—</span>
                                        @endif
                                    </td>

                                    {{-- Terakhir Aktif --}}
                                    <td class="px-6 py-5">
                                        @if($stats && $lastActivity)
                                            <div class="text-xs font-bold text-gray-500">{{ \Carbon\Carbon::parse($lastActivity)->diffForHumans() }}</div>
                                            <div class="text-[9px] text-gray-300 font-bold">{{ \Carbon\Carbon::parse($lastActivity)->format('d M Y') }}</div>
                                        @else
                                            <span class="text-[10px] text-gray-300 italic">Belum pernah aktif</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-2 transition-opacity">
                                            <button onclick="confirmRemoveStudent('{{ $m['user_id'] }}', '{{ $m['name'] }}')" 
                                                    class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:text-rose-600 hover:bg-rose-50 border border-gray-100 hover:border-rose-100 rounded-xl transition-all shadow-sm group/btn"
                                                    title="Keluarkan mahasiswa">
                                                <i data-lucide="user-minus" class="w-4 h-4 text-gray-600 group-hover/btn:text-rose-600"></i>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Keluarkan</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center gap-4">
                                            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center">
                                                <i data-lucide="users" class="w-10 h-10 text-gray-200"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-black text-gray-300">Belum Ada Mahasiswa</h3>
                                                <p class="text-sm text-gray-300 mt-1">Bagikan kode kelas untuk mengundang mahasiswa bergabung.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif


    @if($activeTab === 'grades')
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            @if(!$selectedAssignment)
                <!-- Assignment Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($assignments as $ass)
                        <div wire:click="selectAssignment('{{ $ass['master_soal_id'] }}')" class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-indigo-200 transition-all cursor-pointer group">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                                </div>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ $ass['meeting_title'] }}</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">{{ $ass['master_soal'] }}</h3>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Klik untuk Menilai</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 text-gray-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all"></i>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i data-lucide="inbox" class="w-10 h-10 text-gray-200"></i>
                            </div>
                            <h3 class="text-xl font-black text-gray-400">Belum Ada Tugas</h3>
                            <p class="text-sm text-gray-400 mt-2">Buat pertemuan dengan model pembelajaran untuk menambahkan tugas.</p>
                        </div>
                    @endforelse
                </div>
            @elseif(!$selectedSubmission)
                <!-- Submission List for Selected Assignment -->
                <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-4">
                            <button wire:click="$set('selectedAssignment', null)" class="p-2 border border-gray-200 rounded-xl hover:bg-white transition text-gray-400 hover:text-indigo-600">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            </button>
                            <div>
                                <h2 class="text-xl font-black text-gray-900 tracking-tight">{{ $selectedAssignment['master_soal'] }}</h2>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Daftar mahasiswa yang telah mengerjakan</p>
                            </div>
                        </div>
                        <div class="relative w-64">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" wire:model.live.debounce.300ms="searchSubmission" placeholder="Cari Nama/NIM..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border-gray-100 rounded-xl text-xs font-bold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Mahasiswa</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Status Nilai</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Skor Akhir</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($submissions as $sub)
                                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="px-6 py-4 text-black">
                                            <div class="text-sm font-black">{{ $sub['name'] }}</div>
                                            <div class="text-[10px] font-bold opacity-50">{{ $sub['nim'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($sub['is_fully_graded'])
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[9px] font-black uppercase tracking-widest">Sudah Dinilai</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest">Butuh Koreksi</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-black text-indigo-600">{{ $sub['total_score'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="selectStudent('{{ $sub['user_id'] }}')" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                Detail Penilaian
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Detail Grading Interface -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left: Submission Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 text-black">
                            <div class="flex justify-between items-center mb-8 pb-6 border-b border-gray-50">
                                <div class="flex items-center gap-4">
                                    <button wire:click="$set('selectedSubmission', null)" class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition text-gray-400 font-bold flex items-center gap-2">
                                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                                    </button>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-900">{{ $selectedSubmission['user']['name'] }}</h3>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ $selectedSubmission['user']['mahasiswa']['nim'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-12">
                                @foreach($selectedAssignment['main_soal'] as $main)
                                    @foreach($main['soal'] as $soal)
                                        <div class="space-y-4">
                                            <div class="flex gap-4">
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 font-black text-xs shrink-0">
                                                    {{ $loop->parent->index + 1 }}.{{ $loop->index + 1 }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="prose prose-sm max-w-none text-gray-800 font-medium mb-4">
                                                        {!! $soal['soal'] !!}
                                                    </div>
                                                    
                                                    <!-- Student Answer -->
                                                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                                                        <div class="text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-2">Jawaban Mahasiswa:</div>
                                                        <div class="prose prose-sm max-w-none text-indigo-900 leading-relaxed font-medium">
                                                            {!! $selectedSubmission['answers'][$soal['soal_id']]['jawaban'] ?? 'Tidak ada jawaban.' !!}
                                                        </div>
                                                    </div>

                                                    <!-- AI Review Panel (If available) -->
                                                    @if(isset($aiReviews[$soal['soal_id']]))
                                                        <div class="mt-4 p-5 bg-gradient-to-br from-indigo-600 to-indigo-900 rounded-2xl text-white shadow-xl shadow-indigo-100 animate-in zoom-in duration-300">
                                                            @if(isset($aiReviews[$soal['soal_id']]['loading']))
                                                                <div class="flex items-center gap-3">
                                                                    <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                                                    <span class="text-[10px] font-black uppercase tracking-widest">AI sedang menganalisis jawaban...</span>
                                                                </div>
                                                            @elseif(isset($aiReviews[$soal['soal_id']]['error']))
                                                                <div class="flex items-center gap-2 text-rose-200">
                                                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                                                    <span class="text-[10px] font-bold">{{ $aiReviews[$soal['soal_id']]['error'] }}</span>
                                                                </div>
                                                            @else
                                                                <div class="flex justify-between items-start mb-4">
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="bg-white/20 p-1.5 rounded-lg">
                                                                            <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                                                                        </div>
                                                                        <span class="text-xs font-black uppercase tracking-widest">AI Suggestion</span>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <div class="text-2xl font-black">{{ $aiReviews[$soal['soal_id']]['suggested_score'] }}%</div>
                                                                        <div class="text-[8px] font-black uppercase tracking-widest opacity-60">Skor Rekomendasi</div>
                                                                    </div>
                                                                </div>
                                                                <p class="text-xs text-indigo-100 leading-relaxed mb-4 italic">"{{ $aiReviews[$soal['soal_id']]['feedback'] }}"</p>
                                                                <button wire:click="applyAiReview('{{ $soal['soal_id'] }}')" class="w-full py-2.5 bg-white text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all font-bold">Terapkan Saran AI</button>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right: Grading Controls -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-8 space-y-6">
                            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm text-black">
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <i data-lucide="award" class="w-5 h-5 text-indigo-600"></i> Form Penilaian
                                </h4>
                                
                                <div class="space-y-8">
                                    @foreach($selectedAssignment['main_soal'] as $main)
                                        @foreach($main['soal'] as $soal)
                                            <div class="space-y-4">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Soal {{ $loop->parent->index + 1 }}.{{ $loop->index + 1 }}</span>
                                                    @if(!isset($aiReviews[$soal['soal_id']]) || isset($aiReviews[$soal['soal_id']]['error']))
                                                        <button wire:click="getAiReviewForAssignment('{{ $soal['soal_id'] }}')" 
                                                                wire:loading.attr="disabled"
                                                                class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 transition shadow-sm bg-indigo-50 px-2 py-1 rounded-lg">
                                                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                            <span class="text-[9px] font-black uppercase tracking-widest">Cek AI</span>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <input type="number" wire:model="gradingForm.scores.{{ $soal['soal_id'] }}" placeholder="Skor" class="w-full bg-gray-50 border-gray-100 rounded-xl p-3 text-sm font-black text-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                                                        <span class="text-xs font-bold text-gray-400">/ {{ $soal['bobot'] ?: 100 }}</span>
                                                    </div>
                                                    <x-tiny.tiny-editor 
                                                        dataModel="gradingForm.notes.{{ $soal['soal_id'] }}" 
                                                        height="220"
                                                        toolbar="undo redo | bold italic | bullist numlist | link mathlive | removeformat"
                                                        menubar="false"
                                                    />
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>

                                <div class="mt-10 pt-6 border-t border-gray-50">
                                    <button wire:click="saveGrades" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">Simpan Seluruh Nilai</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
    
    @if($activeTab === 'diskusi')
        <div class="h-[700px] rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-2xl shadow-indigo-100/50 animate-in fade-in zoom-in duration-500">
            @livewire('discussion-hub', ['kelasId' => $classId])
        </div>
    @endif

    @if($activeTab === 'materi')
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex justify-between items-center bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div>
                   <h2 class="text-xl font-black text-gray-900 tracking-tight">Materi Eksternal</h2>
                   <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">File pendukung pembelajaran untuk mahasiswa</p>
                </div>
                @if(!$this->isReadonly)
                    <button wire:click="openExternalMateriModal" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl flex items-center gap-2 hover:bg-indigo-700 shadow-xl shadow-indigo-100 active:scale-95 transition font-black text-[10px] uppercase tracking-widest">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Materi
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($externalMaterials as $materi)
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mt-12 -mr-12 group-hover:bg-indigo-100 transition-colors"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 mb-4">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 mb-2 truncate pr-8">{{ $materi['judul'] }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold mb-6 line-clamp-2">{{ $materi['deskripsi'] }}</p>
                            
                            <div class="flex flex-col gap-3 pt-4 border-t border-gray-50">
                                <div class="flex items-center justify-between">
                                    <a href="{{ Storage::url($materi['link']) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-black text-[10px] uppercase tracking-widest flex items-center gap-1.5 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Pratinjau
                                    </a>
                                    <a href="{{ Storage::url($materi['link']) }}" download class="text-gray-500 hover:text-gray-700 font-black text-[10px] uppercase tracking-widest flex items-center gap-1.5 transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh
                                    </a>
                                </div>
                                @if(!$this->isReadonly)
                                    <div class="flex gap-1">
                                        <button wire:click="openExternalMateriModal('{{ $materi['external_materi_id'] }}')" class="p-1.5 text-gray-400 hover:text-indigo-600 transition">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button onclick="confirmDeleteMateri('{{ $materi['external_materi_id'] }}', '{{ $materi['judul'] }}')" class="p-1.5 text-gray-400 hover:text-rose-600 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 bg-gray-50 rounded-[2.5rem] border border-dashed border-gray-200 text-center">
                        <i data-lucide="folder-open" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">Belum ada materi eksternal.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    @if($activeTab === 'ujian')
        <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            @livewire('dosen.manage-ujian', ['classId' => $classId])
        </div>
    @endif

    @if($activeTab === 'bobot')
        <div class="w-full animate-in fade-in slide-in-from-bottom-4 duration-500">
            @livewire('lecturer-grading-settings', ['kelasId' => $classId])
        </div>
    @endif

    <!-- Modals Section -->
    <!-- Meeting Modal -->
    @if($showMeetingModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 text-black">
            <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col transform transition-all overflow-y-auto">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-gray-800">{{ $meetingForm['id'] ? 'Edit Pertemuan' : 'Buat Pertemuan Baru' }}</h3>
                    <button type="button" wire:click="$set('showMeetingModal', false)" class="text-gray-400 hover:text-gray-600 transition"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                @if(session()->has('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-3 animate-in fade-in duration-300">
                        <i data-lucide="alert-circle" class="w-5 h-5 shadow-sm"></i>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pertemuan</label>
                        <input wire:model="meetingForm.title" placeholder="Misal: Pertemuan 1 - Pengantar" class="border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-lg w-full transition" />
                        @error('meetingForm.title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" wire:model="meetingForm.date" class="border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-lg w-full transition" />
                        @error('meetingForm.date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Pembelajaran</label>
                        <select wire:model.live="meetingForm.learning_model" class="border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-lg w-full transition">
                            <option value="none">Tidak Ada (Reguler)</option>
                            <option value="custom">Custom (Sintaks Mandiri)</option>
                            <option value="pjbl">Project Based Learning (PjBL)</option>
                            <option value="pbl">Problem Based Learning (PBL)</option>
                        </select>
                    </div>

                    <div class="mt-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100/50">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-indigo-100">
                                <i data-lucide="info" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-1">Informasi Sintaks</h4>
                                <p class="text-[11px] text-indigo-700/80 font-medium leading-relaxed">
                                    Setelah menyimpan pertemuan ini, Anda akan diarahkan ke <b>Flow Builder</b> untuk mengatur detail langkah pembelajaran, materi, dan penugasan secara lebih mendalam.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showMeetingModal', false)" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg font-medium transition">Batal</button>
                        <button type="button" wire:click="saveMeeting" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg hover:bg-indigo-700 font-medium transition shadow-sm flex items-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveMeeting"><i data-lucide="save" class="w-4 h-4"></i> Simpan</span>
                            <span wire:loading wire:target="saveMeeting">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif



    <!-- Duplication Modal -->
    @if($showDuplicateModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 text-black">
            <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg transform transition-all">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="copy" class="w-5 h-5 text-indigo-600"></i> Duplikasi Sintaks
                    </h3>
                    <button wire:click="$set('showDuplicateModal', false)" class="text-gray-400 hover:text-gray-600 transition"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                
                <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl mb-6 text-sm text-indigo-800">
                    <p class="font-medium">Seluruh materi, tugas, diskusi, kuis, dan soal pilihan ganda di pertemuan ini akan diduplikasi secara mendalam.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Kelas Tujuan</label>
                        <select wire:model.live="duplicateTargetClassId" class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-lg transition bg-white cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($availableClasses as $c)
                                <option value="{{ $c['kelas_id'] ?? ($c['id'] ?? '') }}">
                                    {{ $c['mata_kuliah']['mata_kuliah'] ?? 'Mata Kuliah' }} - {{ $c['kelas'] ?? ($c['nama_kelas'] ?? ($c['name'] ?? 'Kelas')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(!empty($duplicateTargetClassId))
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Pertemuan Tujuan</label>
                            <select wire:model="duplicateTargetMeetingId" class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-lg transition bg-white cursor-pointer">
                                <option value="">-- Pilih Pertemuan --</option>
                                @foreach($availableMeetings as $m)
                                    <option value="{{ $m['pertemuan_id'] ?? ($m['id'] ?? '') }}">{{ $m['pertemuan'] ?? ($m['judul_pertemuan'] ?? ($m['title'] ?? 'Pertemuan Tanpa Judul')) }}</option>
                                @endforeach
                            </select>
                            @error('duplicateTargetMeetingId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                    <button wire:click="$set('showDuplicateModal', false)" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg font-medium transition">Batal</button>
                    <button wire:click="confirmDuplicate" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg hover:bg-indigo-700 font-medium transition shadow-sm flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Konfirmasi Duplikasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- External Materi Modal -->
    <!-- External Materi Modal -->
    @if($showExternalMateriModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="link" class="w-5 h-5 text-indigo-600"></i> {{ $externalMateriForm['id'] ? 'Edit' : 'Tambah' }} Materi Eksternal
                    </h3>
                    <button wire:click="$set('showExternalMateriModal', false)" class="text-gray-400 hover:text-gray-600 transition"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                
                <form wire:submit.prevent="saveExternalMateri" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Judul Materi</label>
                        <input type="text" wire:model="externalMateriForm.judul" class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 p-2.5 rounded-xl transition outline-none" placeholder="Contoh: Dokumentasi Laravel">
                        @error('externalMateriForm.judul') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">File Materi</label>
                        <input type="file" wire:model="externalMateriForm.link" class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 p-2.5 rounded-xl transition outline-none">
                        <div wire:loading wire:target="externalMateriForm.link" class="text-xs text-indigo-600 font-medium mt-1">Mengunggah file...</div>
                        @error('externalMateriForm.link') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
                        <textarea wire:model="externalMateriForm.deskripsi" rows="3" class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 p-2.5 rounded-xl transition outline-none resize-none" placeholder="Jelaskan sedikit tentang materi ini..."></textarea>
                        @error('externalMateriForm.deskripsi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showExternalMateriModal', false)" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition">Batal</button>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 font-bold transition shadow-md shadow-indigo-100 flex items-center gap-2">
                             <i data-lucide="save" class="w-4 h-4"></i> Simpan Materi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
                            <input type="text" value="{{ $classData['course_name'] }}" disabled class="w-full bg-gray-100 border-gray-100 rounded-2xl p-4 text-sm font-medium opacity-70">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kelas</label>
                            <input type="text" value="{{ $classData['name'] }}" disabled class="w-full bg-gray-100 border-gray-100 rounded-2xl p-4 text-sm font-medium opacity-70">
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
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot Kategori (%)</label>
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
    @push('scripts')
        <script>
        function confirmDeleteMeeting(id, title) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Pertemuan?',
                    message: `Apakah Anda yakin ingin menghapus "${title}"? Seluruh sintaks, materi, dan tugas di dalamnya akan ikut terhapus secara permanen.`,
                    confirm: true,
                    confirmText: 'Ya, Hapus Semua',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteMeeting',
                        params: [id]
                    }
                }
            }));
        }

        function confirmDeleteMateri(id, title) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Materi?',
                    message: `Apakah Anda yakin ingin menghapus materi "${title}"?`,
                    confirm: true,
                    confirmText: 'Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteExternalMateri',
                        params: [id]
                    }
                }
            }));
        }
        </script>
    @endpush
    @script
    <script>
        window.confirmRejectEnrollment = function(id, name) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Tolak Permintaan?',
                    message: `Tolak permintaan mahasiswa ${name} masuk ke kelas ini?`,
                    confirm: true,
                    confirmText: 'Ya, Tolak',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'rejectEnrollment',
                        params: [id]
                    }
                }
            }));
        }

        window.confirmRemoveStudent = function(id, name) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Keluarkan Mahasiswa?',
                    message: `Yakin ingin mengeluarkan ${name} dari kelas? Status kelas lama dan nilainya pada kelas ini tidak akan terhapus secara otomatis kecuali di-reset.`,
                    confirm: true,
                    confirmText: 'Keluarkan',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'removeStudent',
                        params: [id]
                    }
                }
            }));
        }
    </script>
    @endscript
</div>
