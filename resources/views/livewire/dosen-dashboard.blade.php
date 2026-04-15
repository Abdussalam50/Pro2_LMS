<div class="p-0 md:p-8 max-w-[1600px] mx-auto min-h-screen bg-[#fafbfc]">
    <!-- Header with Tab Navigation -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-12 gap-6 relative">
        <div class="relative z-10">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50/80 backdrop-blur-md text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4 border border-indigo-100/50 shadow-sm">
                <i data-lucide="layout-dashboard" class="w-3 h-3"></i> Workspace Dosen
            </div>
            <h1
                class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-2 bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-indigo-900 to-gray-900">
                Manajemen Kelas
            </h1>
            <p class="text-sm text-gray-400 font-medium max-w-md">Pusat kendali untuk mata kuliah, kelas, dan pengumuman
                akademik Anda dengan antarmuka cerdas.</p>
        </div>

        <div
            class="flex items-center bg-white/40 backdrop-blur-xl p-1.5 rounded-2xl border border-gray-200 shadow-xl shadow-gray-200/20 mb-4 overflow-x-auto gap-2">
            
            {{-- Tabs Group --}}
            <div class="flex items-center gap-1">
                <button wire:click="setTab('courses')"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black transition-all uppercase tracking-tighter flex items-center gap-2 {{ $activeTab === 'courses' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600 hover:bg-white/50' }}">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    Matkul & Kelas
                </button>
                <button wire:click="setTab('announcements')"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black transition-all uppercase tracking-tighter flex items-center gap-2 {{ $activeTab === 'announcements' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600 hover:bg-white/50' }}">
                    <i wire:ignore data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                    Pengumuman
                </button>
                <button wire:click="setTab('scheduled_notifications')"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black transition-all uppercase tracking-tighter flex items-center gap-2 {{ $activeTab === 'scheduled_notifications' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600 hover:bg-white/50' }}">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    Jadwal Notif
                </button>
                <button wire:click="setTab('exams')"
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black transition-all uppercase tracking-tighter flex items-center gap-2 {{ $activeTab === 'exams' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600 hover:bg-white/50' }}">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    Ujian
                </button>
            </div>

            {{-- Vertical Divider --}}
            <div class="w-px h-8 bg-gray-200 mx-2 hidden sm:block"></div>

            {{-- Period Selector Group --}}
            <div class="flex items-center gap-3 pr-2 min-w-[240px]">
                <div class="relative w-full">
                    <select wire:model.live="selectedPeriod" class="w-full bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 appearance-none pr-8 cursor-pointer text-indigo-600 hover:text-indigo-800 transition-colors">
                        <option value="active">📅 Sem. Aktif ({{ $activePeriodData?->name ?? 'None' }})</option>
                        @foreach($this->availablePeriods as $p)
                            @if(!$p->is_active)
                                <option value="{{ $p->id }}" class="text-gray-900">📚 {{ $p->name }} (Arsip)</option>
                            @endif
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-indigo-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
                
                @if($this->isReadonly)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-100 text-[9px] font-black text-rose-600 uppercase tracking-widest whitespace-nowrap animate-pulse">
                        <i data-lucide="lock" class="w-3 h-3"></i> Read-Only
                    </div>
                @endif
            </div>
        </div>

        <!-- Decorative backgrounds -->
        <div
            class="absolute -top-10 -left-10 w-64 h-64 bg-indigo-100/30 rounded-full blur-3xl -z-10 pointer-events-none">
        </div>
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-blue-50/40 rounded-full blur-3xl -z-10 pointer-events-none">
        </div>
    </div>

    <!-- Main Content Area -->
    <div
        class="bg-white/70 backdrop-blur-sm rounded-xl shadow-[0_32px_64px_-16px_rgba(0,0,0,0.05)] border border-white/80 overflow-hidden relative mt-4 border-gray-200 shadow-sm shadow-gray-200/20 rounded-[3rem]">
        @if($activeTab === 'courses')
            <div class="p-6 md:p-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Pengajaran</h3>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">Kelola kurikulum dan
                            rombongan belajar Anda</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                        @if(!$this->isReadonly)
                            <button wire:click="openExternalMateriModal"
                                class="group flex items-center justify-center gap-2 px-5 py-3 bg-white border border-amber-100 text-amber-600 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-amber-50 transition-all shadow-sm hover:shadow-md active:scale-95 w-full sm:w-auto">
                                <div class="p-1.5 rounded-lg bg-amber-50 group-hover:bg-amber-100 transition-colors">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                </div>
                                Upload Materi
                            </button>
                            <button wire:click="openCourseModal"
                                class="group flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 w-full sm:w-auto">
                                <div class="p-1.5 rounded-lg bg-white/10 group-hover:bg-white/20 transition-colors">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                </div>
                                Tambah Matkul
                            </button>
                        @endif
                    </div>
                </div>

                @if(count($this->courses) === 0)
                    <div
                        class="flex flex-col items-center justify-center py-32 bg-gray-50/50 rounded-[3rem] border-2 border-dashed border-gray-100">
                        <div class="relative mb-8">
                            <div class="absolute inset-0 bg-indigo-100 blur-2xl opacity-50 rounded-full animate-pulse"></div>
                            <div class="relative p-8 rounded-[2.5rem] bg-white shadow-xl border border-gray-50">
                                <i data-lucide="book-open" class="w-16 h-16 text-indigo-400 font-thin"></i>
                            </div>
                        </div>
                        <p class="text-gray-400 font-black uppercase tracking-[0.3em] text-[11px] mb-2 text-center">Belum ada
                            mata kuliah aktif</p>
                        <p class="text-gray-300 text-xs font-medium mb-8 text-center max-w-xs leading-relaxed">Mulai perjalanan
                            akademik Anda dengan menambahkan kurikulum pertama.</p>
                        <button wire:click="openCourseModal"
                            class="px-8 py-3 bg-white text-indigo-600 border border-indigo-100 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-50 transition-all shadow-sm">Buat
                            Mata Kuliah</button>
                    </div>
                @else
                    <div class="grid grid-cols-1 my-4 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($this->courses as $course)
                            <div wire:key="course-{{ $course['id'] }}"
                                class="flex flex-col p-6 md:p-8 rounded-2xl md:rounded-[3rem] border border-indigo-100/50 shadow-sm hover:border-indigo-100 transition-all group relative overflow-hidden bg-gradient-to-br from-white via-white to-indigo-50/40 hover:shadow-[0_40px_80px_-24px_rgba(79,70,229,0.12)]">
                                <div class="flex justify-between items-start mb-10 relative z-10">
                                    <div
                                        class="px-4 py-2 rounded-2xl bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest border border-indigo-100/50 shadow-sm">
                                        {{ $course['code'] }}
                                    </div>
                                    @if(!$this->isReadonly)
                                        <button wire:click="deleteCourse('{{ $course['id'] }}')"
                                            class="opacity-0 group-hover:opacity-100 w-10 h-10 rounded-2xl bg-rose-50 text-rose-300 hover:text-rose-600 hover:bg-rose-100 transition-all flex items-center justify-center">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>

                                <h4
                                    class="font-black text-gray-900 text-2xl mb-3 leading-[1.1] group-hover:text-indigo-600 transition-colors">
                                    {{ $course['name'] }}</h4>
                                <p class="text-xs text-gray-400 font-medium mb-10 line-clamp-2 leading-relaxed">
                                    {{ $course['description'] }}</p>

                                <div class="mt-auto pt-8 border-t border-gray-50">
                                    <div
                                        class="flex items-center justify-between text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6">
                                        <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                            Daftar Kelas</span>
                                        @if(!$this->isReadonly)
                                            <button wire:click="openClassModal('{{ $course['id'] }}')"
                                                class="text-indigo-600 hover:text-indigo-800 transition-all hover:tracking-[0.25em] shadow-sm border border-gray-300 p-2 rounded-xl bg-gray-50">+
                                                TAMBAH</button>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2.5">
                                        @forelse($course['classes'] as $cls)
                                            <div class="flex items-center gap-1.5 group/class">
                                                <a href="/dosen/classes/{{ $cls['id'] }}"
                                                    class="inline-flex items-center px-5 py-2.5 rounded-2xl bg-gray-50 text-gray-700 text-[11px] font-black uppercase tracking-tighter hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-gray-100 group-hover/class:border-indigo-200 group-hover/class:shadow-md">
                                                    {{ $cls['name'] }} <span class="ml-1 opacity-50">({{ $cls['code'] }})</span>
                                                    <i data-lucide="chevron-right"
                                                        class="w-3.5 h-3.5 ml-2 transition-transform group-hover:translate-x-1"></i>
                                                </a>
                                                @if(!$this->isReadonly)
                                                    <button wire:click="openNotificationModal('{{ $cls['id'] }}')"
                                                        class="w-10 h-10 flex items-center justify-center rounded-2xl text-gray-300 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition-all"
                                                        title="Kirim Notifikasi Langsung">
                                                        <i data-lucide="bell" class="w-4 h-4"></i>
                                                    </button>
                                                    <button wire:click="openUjianModal('{{ $cls['id'] }}')"
                                                        class="w-10 h-10 flex items-center justify-center rounded-2xl text-gray-300 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition-all"
                                                        title="Tambah Ujian">
                                                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @empty
                                            <div
                                                class="w-full py-4 text-center rounded-2xl bg-gray-50/50 border border-dashed border-gray-100">
                                                <span class="text-[9px] text-gray-300 font-black uppercase tracking-widest italic">Belum
                                                    ada kelas</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Accent decoration -->
                                <div
                                    class="absolute bottom-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-tl-[4rem] group-hover:bg-indigo-500/10 transition-colors -z-10">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'announcements')
            <div class="p-6 md:p-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">Siaran Pengumuman</h3>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">Komunikasi terpusat
                            untuk seluruh mahasiswa</p>
                    </div>
                    <button wire:click="openAnnouncementModal"
                        class="group flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 w-full md:w-auto">
                        <div class="p-1.5 rounded-lg bg-white/10 group-hover:bg-white/20 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </div>
                        Buat Pengumuman
                    </button>
                </div>

                @if(count($this->announcements) === 0)
                    <div
                        class="flex flex-col items-center justify-center py-32 bg-gray-50/50 rounded-[3rem] border-2 border-dashed border-gray-100 opacity-60">
                        <div class="p-8 rounded-[2.5rem] bg-white shadow-xl border border-gray-50 mb-8">
                            <i data-lucide="megaphone" class="w-16 h-16 text-gray-200 font-thin"></i>
                        </div>
                        <p class="font-black text-[11px] uppercase tracking-[0.2em] text-gray-400">Belum ada pengumuman
                            disiarkan</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($this->announcements as $ann)
                            <div wire:key="ann-{{ $ann['id'] }}"
                                class="p-6 md:p-8 rounded-2xl md:rounded-[2.5rem] border border-gray-50 hover:border-indigo-100 transition-all bg-white hover:shadow-[0_40px_80px_-24px_rgba(79,70,229,0.08)] group relative overflow-hidden">
                                <div class="flex flex-col md:flex-row justify-between items-start gap-6 relative z-10">
                                    <div class="flex items-center gap-6">
                                        <div
                                            class="w-16 h-16 rounded-[1.5rem] bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 transform group-hover:rotate-6">
                                            <i data-lucide="megaphone" class="w-7 h-7"></i>
                                        </div>
                                        <div>
                                            <h4
                                                class="font-black text-gray-900 text-xl mb-1.5 tracking-tight px-1 transition-colors group-hover:text-indigo-600">
                                                {{ $ann['title'] }}</h4>
                                            <div
                                                class="flex items-center gap-3 text-[10px] text-gray-400 font-black uppercase tracking-[0.15em] bg-gray-50/50 px-3 py-1 rounded-full border border-gray-100 w-fit">
                                                <span class="flex items-center gap-1.5 text-indigo-500"><i data-lucide="user"
                                                        class="w-3 h-3"></i> {{$ann['author_name']}}</span>
                                                <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                                                <span class="flex items-center gap-1.5"><i data-lucide="calendar"
                                                        class="w-3 h-3"></i> {{ $ann['created_at'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button wire:click="deleteAnnouncement('{{ $ann['id'] }}')"
                                        class="w-10 h-10 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-300 hover:text-rose-600 hover:bg-rose-100 transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <div
                                    class="mt-8 pl-0 md:pl-[88px] text-gray-600 leading-[1.8] font-medium text-sm relative z-10 italic">
                                    "{{ $ann['content'] }}"
                                </div>
                                <!-- Background accent -->
                                <div
                                    class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-bl-[8rem] -z-0 group-hover:bg-indigo-500/10 transition-colors">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'scheduled_notifications')
            <div class="p-6 md:p-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight">Automasi Notifikasi</h3>
                            <span
                                class="px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-[9px] font-black text-indigo-600 uppercase tracking-[0.15em] animate-pulse">Smart
                                Scheduler Active</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">Pantau dan kelola jadwal
                            pengiriman pesan otomatis</p>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-[2.5rem] border border-gray-50 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.03)] bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] bg-gray-50/50 border-b border-gray-50">
                                    <th class="py-7 px-10 whitespace-nowrap">Status Alur</th>
                                    <th class="py-7 px-10 whitespace-nowrap">Waktu Kirim</th>
                                    <th class="py-7 px-10 whitespace-nowrap">Target Kelas</th>
                                    <th class="py-7 px-10 whitespace-nowrap">Intisari Pesan</th>
                                    <th class="py-7 px-10 whitespace-nowrap text-right">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @forelse($this->scheduledNotifications as $notif)
                                    <tr wire:key="sched-notif-{{ $notif->id }}"
                                        class="hover:bg-indigo-50/10 transition-all group">
                                        <td class="py-8 px-10">
                                            @if($notif->status === 'active')
                                                <div
                                                    class="inline-flex items-center px-4 py-2 rounded-2xl text-[10px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100/50 shadow-sm group-hover:bg-emerald-100/50 transition-colors">
                                                    <span
                                                        class="w-2 h-2 rounded-full bg-emerald-500 mr-2.5 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                                    RUNNING
                                                </div>
                                            @else
                                                <span
                                                    class="px-4 py-2 rounded-2xl text-[10px] font-black bg-gray-100 text-gray-400 border border-gray-200/50">
                                                    {{ strtoupper($notif->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-8 px-10">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                                                    <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-[11px] font-black text-gray-900 mb-0.5">
                                                        {{ $notif->waktu_kirim->format('d M Y') }}</div>
                                                    <div class="text-[10px] font-bold text-indigo-500">
                                                        {{ $notif->waktu_kirim->format('H:i') }} WIB</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-8 px-10">
                                            <div class="text-xs font-black text-gray-900 mb-1 flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-indigo-400"></div>
                                                {{ $notif->kelas->kelas }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider pl-3.5">
                                                {{ $notif->kelas->mataKuliah->mata_kuliah }}</div>
                                        </td>
                                        <td class="py-8 px-10">
                                            <div class="text-xs font-black text-gray-900 mb-1.5 truncate max-w-[200px] hover:text-indigo-600 transition-colors cursor-default"
                                                title="{{ $notif->judul }}">{{ $notif->judul }}</div>
                                            <div
                                                class="text-[10px] text-gray-400 line-clamp-1 max-w-[250px] leading-relaxed italic">
                                                "{{ $notif->isi }}"</div>
                                        </td>
                                        <td class="py-8 px-10 text-right">
                                            <button wire:click="deleteScheduledNotification('{{ $notif->id }}')"
                                                class="w-11 h-11 flex items-center justify-center rounded-2xl bg-white border border-gray-100 text-gray-300 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all shadow-sm hover:shadow-md active:scale-95">
                                                <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-32 text-center">
                                            <div class="relative w-fit mx-auto mb-8">
                                                <div class="absolute inset-0 bg-gray-100 blur-2xl opacity-50 rounded-full">
                                                </div>
                                                <i data-lucide="calendar-x"
                                                    class="relative w-16 h-16 text-gray-200 font-thin mx-auto"></i>
                                            </div>
                                            <p class="font-black text-[11px] uppercase tracking-[0.3em] text-gray-300">
                                                Penjadwalan Kosong</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'exams')
            <div class="p-6 md:p-12">
                @livewire('dosen.manage-ujian')
            </div>
        @endif
    </div>

    <!-- Modals -->
    <!-- Course Modal -->
    @if($showCourseModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl p-6 md:p-8 rounded-2xl md:rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-md border border-white/40 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Tambah Mata Kuliah</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Definisikan
                            kurikulum baru</p>
                    </div>
                </div>
                <form wire:submit.prevent="saveCourse" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama
                            Mata Kuliah</label>
                        <input wire:model="courseForm.name"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: Kalkulus Lanjut" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kode
                            Matkul</label>
                        <input wire:model="courseForm.code"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: MKN-301" required />
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi
                            Singkat</label>
                        <textarea wire:model="courseForm.description"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-24"
                            placeholder="Opsional..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showCourseModal', false)"
                            class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">Simpan
                            Matkul</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Class Modal -->
    @if($showClassModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl p-6 md:p-8 rounded-2xl md:rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-md border border-white/40 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Tambah Kelas</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Definisikan
                            rombongan belajar</p>
                    </div>
                </div>
                <form wire:submit.prevent="saveClass" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama
                            Kelas</label>
                        <input wire:model="classForm.name"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: Regular A" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kode
                            Kelas</label>
                        <input wire:model="classForm.code"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: REG-A-2024" required />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showClassModal', false)"
                            class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">Simpan
                            Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Announcement Modal -->
    @if($showAnnouncementModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl p-6 md:p-8 rounded-2xl md:rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-lg border border-white/40 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="megaphone" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Buat Pengumuman</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Siarkan pesan ke
                            seluruh mahasiswa</p>
                    </div>
                </div>
                <form wire:submit.prevent="saveAnnouncement" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Judul
                            Pengumuman</label>
                        <input wire:model="announcementForm.title"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Ketik judul yang menarik..." required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Isi
                            Pengumuman</label>
                        <textarea wire:model="announcementForm.content"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-40"
                            placeholder="Tuliskan detail pengumuman di sini..." required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showAnnouncementModal', false)"
                            class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit"
                            class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            Terbitkan Sekarang
                            <i data-lucide="send"
                                class="w-3.5 h-3.5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- External Materi Modal -->
    @if($showExternalMateriModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-2xl md:rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-md border border-white/40 max-h-[90vh] flex flex-col overflow-y-auto">
                <div class="p-6 md:p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30 sticky top-0 z-10 backdrop-blur-lg">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 tracking-tight">Upload Materi</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Bagikan modul
                                pembelajaran</p>
                        </div>
                    </div>
                    <button wire:click="$set('showExternalMateriModal', false)"
                        class="w-8 h-8 flex items-center justify-center rounded-xl bg-white text-gray-400 hover:text-gray-600 transition-colors shadow-sm border border-gray-50"><i
                            data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form wire:submit.prevent="saveExternalMateri" class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Pilih
                            Mata Kuliah</label>
                        <div class="relative">
                            <select wire:model="externalMateriForm.mata_kuliah_id"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                <option value="">-- Pilih Matkul --</option>
                                @foreach($this->courses as $course)
                                    <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                                @endforeach
                            </select>
                            @error('externalMateriForm.mata_kuliah_id') <span
                            class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Judul
                            Materi</label>
                        <input type="text" wire:model="externalMateriForm.judul"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: Slide Pertemuan 1">
                        @error('externalMateriForm.judul') <span
                        class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">File
                            (PDF/Modul)</label>
                        <div
                            class="group relative px-4 py-8 border-2 border-dashed border-gray-100 rounded-[2rem] bg-gray-50/30 hover:bg-indigo-50/30 hover:border-indigo-200 transition-all text-center">
                            <input type="file" wire:model="externalMateriForm.link"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            <div class="space-y-2">
                                <i data-lucide="file-up"
                                    class="w-8 h-8 text-gray-300 mx-auto group-hover:text-indigo-400 transition-colors mb-2"></i>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Klik atau tarik
                                    file ke sini</p>
                                <p class="text-[9px] text-gray-400" wire:loading.remove
                                    wire:target="externalMateriForm.link">Maksimal 20MB</p>
                                <div wire:loading wire:target="externalMateriForm.link"
                                    class="text-[9px] font-black text-indigo-600 animate-pulse uppercase tracking-widest">
                                    Memproses File...</div>
                            </div>
                        </div>
                        @error('externalMateriForm.link') <span
                        class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi</label>
                        <textarea wire:model="externalMateriForm.deskripsi"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-24"
                            placeholder="Tulis deskripsi materi..."></textarea>
                        @error('externalMateriForm.deskripsi') <span
                        class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-indigo-600 disabled:bg-gray-400 text-white p-4 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="saveExternalMateri">Upload & Simpan</span>
                        <span wire:loading wire:target="saveExternalMateri">Menyimpan...</span>
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin" wire:loading
                            wire:target="saveExternalMateri"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Notification Modal (Scheduled) -->
    @if($showNotificationModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl p-6 md:p-8 rounded-2xl md:rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-md border border-white/40 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Kirim Notifikasi</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Berikan informasi
                            instan ke mahasiswa</p>
                    </div>
                </div>
                <form wire:submit.prevent="sendClassNotification" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Judul
                            Notifikasi</label>
                        <input wire:model="notificationForm.title"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Pesan penting untuk kelas..." required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Isi
                            Pesan</label>
                        <textarea wire:model="notificationForm.body"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-28"
                            placeholder="Ketik pesan lengkap di sini..." required></textarea>
                    </div>

                    <div
                        class="group flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-indigo-50/50 hover:border-indigo-100 transition-all cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="relative flex items-center">
                                <input type="checkbox" wire:model.live="notificationForm.is_scheduled" id="is_scheduled"
                                    class="peer h-5 w-5 opacity-0 absolute z-10 cursor-pointer" />
                                <div
                                    class="h-5 w-5 bg-white border border-gray-200 rounded-lg peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all flex items-center justify-center">
                                    <i data-lucide="check"
                                        class="w-3.5 h-3.5 text-white scale-0 peer-checked:scale-100 transition-transform"></i>
                                </div>
                            </div>
                            <label for="is_scheduled"
                                class="text-[10px] font-black text-gray-500 uppercase tracking-widest cursor-pointer group-hover:text-indigo-600">Jadwalkan
                                Pengiriman</label>
                        </div>
                        <i data-lucide="clock" class="w-4 h-4 text-gray-300 group-hover:text-indigo-400"></i>
                    </div>

                    @if($notificationForm['is_scheduled'])
                        <div class="grid grid-cols-2 gap-4 animate-in slide-in-from-top-4 duration-300">
                            <div>
                                <label
                                    class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Waktu
                                    Kirim</label>
                                <input type="datetime-local" wire:model="notificationForm.scheduled_at"
                                    class="w-full bg-white border-gray-100 rounded-xl p-3 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20" />
                            </div>
                            <div>
                                <label
                                    class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Perulangan</label>
                                <select wire:model="notificationForm.recurrence"
                                    class="w-full bg-white border-gray-100 rounded-xl p-3 text-xs font-medium focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="none">Hanya Sekali</option>
                                    <option value="daily">Setiap Hari</option>
                                    <option value="weekly">Setiap Minggu</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showNotificationModal', false)"
                            class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit"
                            class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            {{ $notificationForm['is_scheduled'] ? 'Jadwalkan' : 'Kirim Sekarang' }}
                            <i data-lucide="{{ $notificationForm['is_scheduled'] ? 'calendar-plus' : 'send' }}"
                                class="w-3.5 h-3.5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Ujian Modal -->
    @if($showUjianModal)
        <div
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div
                class="bg-white/90 backdrop-blur-xl p-6 md:p-8 rounded-2xl md:rounded-[1.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-2xl border border-white/40 overflow-y-auto max-h-[90vh]">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">
                            {{ $ujianForm['ujian_id'] ? 'Edit Ujian' : 'Tambah Ujian Baru' }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Konfigurasi
                            parameter ujian mahasiswa</p>
                    </div>
                </div>

                <form wire:submit.prevent="saveUjian" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Mata
                                Kuliah</label>
                            <select wire:model="ujianForm.mata_kuliah_id"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                <option value="">-- Pilih Matkul --</option>
                                @foreach($this->courses as $course)
                                    <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kelas</label>
                            <select wire:model="ujianForm.kelas_id"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
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
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama
                            Ujian</label>
                        <input type="text" wire:model="ujianForm.nama_ujian"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Contoh: UTS Aljabar Linear">
                        @error('ujianForm.nama_ujian') <span
                        class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi
                            & Instruksi</label>
                        <textarea wire:model="ujianForm.deskripsi"
                            class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-24"
                            placeholder="Tulis instruksi pengerjaan..."></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jenis</label>
                            <select wire:model="ujianForm.jenis_ujian"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                                <option value="kuis">Kuis</option>
                                <option value="tugas">Tugas</option>
                                <option value="uts">UTS</option>
                                <option value="uas">UAS</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jumlah
                                Soal</label>
                            <input type="number" wire:model="ujianForm.jumlah_soal"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot Kategori
                                (%)</label>
                            <input type="number" wire:model="ujianForm.bobot_nilai"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu
                                Mulai</label>
                            <input type="datetime-local" wire:model="ujianForm.waktu_mulai"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu
                                Selesai</label>
                            <input type="datetime-local" wire:model="ujianForm.waktu_selesai"
                                class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50/50 rounded-[2rem] border border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="ujianForm.is_active"
                                class="peer h-5 w-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Aktif</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="ujianForm.is_open"
                                class="peer h-5 w-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Buka
                                Akses</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Mode
                            Batasan Ujian</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Mode Terbuka (Open All) -->
                            <label
                                class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-gray-50 {{ $ujianForm['mode_batasan'] === 'open' ? 'border-indigo-600 bg-indigo-50/30 shadow-md shadow-indigo-100/50' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="open"
                                    class="peer sr-only">
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-8 h-8 rounded-xl flex items-center justify-center {{ $ujianForm['mode_batasan'] === 'open' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-50 text-gray-400' }}">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </div>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'open' ? 'text-indigo-900' : 'text-gray-600' }}">Terbuka</span>
                                </div>
                                <p class="text-[10px] text-gray-400 leading-relaxed font-medium">Ujian bebas hambatan.
                                    Mahasiswa dapat membuka tab baru dan mengakses sumber eksternal.</p>
                                @if($ujianForm['mode_batasan'] === 'open')
                                    <div class="absolute top-4 right-4 text-indigo-600"><i data-lucide="check-circle-2"
                                            class="w-5 h-5"></i></div>
                                @endif
                            </label>

                            <!-- Mode Buka Materi (Materi Only) -->
                            <label
                                class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-amber-50 {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'border-amber-500 bg-amber-50/50 shadow-md shadow-amber-100/50' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="materi_only"
                                    class="peer sr-only">
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-8 h-8 rounded-xl flex items-center justify-center {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'bg-amber-100 text-amber-600' : 'bg-gray-50 text-gray-400' }}">
                                        <i data-lucide="book-open" class="w-4 h-4"></i>
                                    </div>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'text-amber-900' : 'text-gray-600' }}">Buka
                                        Materi</span>
                                </div>
                                <p class="text-[10px] text-gray-400 leading-relaxed font-medium">Izinkan mahasiswa membuka
                                    hanya dokumen materi PDF yang dilampirkan via LMS.</p>
                                @if($ujianForm['mode_batasan'] === 'materi_only')
                                    <div class="absolute top-4 right-4 text-amber-500"><i data-lucide="check-circle-2"
                                            class="w-5 h-5"></i></div>
                                @endif
                            </label>

                            <!-- Mode Ketat (Strict Lockdown) -->
                            <label
                                class="relative flex flex-col p-5 cursor-pointer rounded-2xl border-2 transition-all hover:bg-rose-50 {{ $ujianForm['mode_batasan'] === 'strict' ? 'border-rose-500 bg-rose-50/50 shadow-md shadow-rose-100/50' : 'border-gray-100 bg-white' }}">
                                <input type="radio" wire:model.live="ujianForm.mode_batasan" value="strict"
                                    class="peer sr-only">
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-8 h-8 rounded-xl flex items-center justify-center {{ $ujianForm['mode_batasan'] === 'strict' ? 'bg-rose-100 text-rose-600' : 'bg-gray-50 text-gray-400' }}">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'strict' ? 'text-rose-900' : 'text-gray-600' }}">Ketat
                                        (Lock)</span>
                                </div>
                                <p class="text-[10px] text-gray-400 leading-relaxed font-medium">Layar penuh dikunci. Blokir
                                    copy-paste, pindah tab akan mendiskualifikasi peserta.</p>
                                @if($ujianForm['mode_batasan'] === 'strict')
                                    <div class="absolute top-4 right-4 text-rose-500"><i data-lucide="check-circle-2"
                                            class="w-5 h-5"></i></div>
                                @endif
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 sticky bottom-0 bg-white/80 backdrop-blur-md pb-4">
                        <button type="button" wire:click="$set('showUjianModal', false)"
                            class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit"
                            class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            {{ $ujianForm['ujian_id'] ? 'Update Ujian' : 'Simpan Ujian' }}
                            <i data-lucide="save" class="w-3.5 h-3.5 transition-transform group-hover:scale-110"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        // Lucide is now handled globally in app.js
    </script>
</div>