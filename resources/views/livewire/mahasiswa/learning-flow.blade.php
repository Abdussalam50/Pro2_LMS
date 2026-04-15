<div class="p-0 md:p-8 max-w-[1600px] mx-auto min-h-screen bg-[#fafbfc]">

    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <a href="/mahasiswa/classes" class="inline-flex items-center text-[10px] font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors mb-4">
            <i wire:ignore data-lucide="arrow-left" class="w-3.5 h-3.5 mr-2"></i> Kembali ke Kelas
        </a>
        <div class="flex items-center gap-3 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2">
            Kelas Rekayasa Perangkat Lunak <i wire:ignore data-lucide="chevron-right" class="w-3 h-3"></i> Pertemuan 3
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight">Strategi Pembelajaran (Flow)</h1>
        @if($this->isReadonly)
            <div class="mt-4 flex items-center gap-3 bg-amber-50 border border-amber-200 p-4 rounded-2xl text-amber-800 shadow-sm">
                <i wire:ignore data-lucide="archive" class="w-5 h-5 text-amber-600"></i>
                <div class="text-[10px] font-black uppercase tracking-widest">Mode Arsip: Lihat Saja</div>
            </div>
        @endif
    </div>

    @if (session()->has('success_message'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 text-sm font-semibold animate-in fade-in slide-in-from-top-4">
            <i wire:ignore data-lucide="award" class="w-6 h-6 text-emerald-500"></i>
            {{ session('success_message') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- Left Panel: Stepper -->
        <div class="w-full lg:w-1/4 shrink-0 lg:sticky lg:top-8 space-y-2">
            <div class="p-6 bg-white/80 backdrop-blur-md rounded-[2rem] border border-gray-100 shadow-[0_20px_40px_-12px_rgba(0,0,0,0.03)] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-indigo-50/50 rounded-br-full -z-10 pointer-events-none"></div>
                
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i wire:ignore data-lucide="route" class="w-4 h-4"></i>
                    Alur Pembelajaran
                </h3>

                <div class="relative">
                    <!-- Vertical Line -->
                    <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-gray-100 -z-0"></div>

                    <div class="space-y-6 relative z-10">
                        @foreach($tahapanList as $tahap)
                            @php
                                $isActive = $tahap['id'] === $activeTahapId;
                                $isCompleted = $tahap['status'] === 'completed';
                                $isLocked = $tahap['status'] === 'locked';
                            @endphp
                            
                            <button 
                                wire:click="setActiveTahap('{{ $tahap['id'] }}')" 
                                class="w-full text-left flex gap-4 group {{ $isLocked ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:-translate-y-0.5 transition-transform' }}"
                                {{ $isLocked ? 'disabled' : '' }}
                            >
                                <!-- Icon / Indicator -->
                                <div class="w-12 h-12 rounded-2xl shrink-0 flex items-center justify-center transition-all shadow-sm
                                    @if($isCompleted) bg-emerald-50 text-emerald-500 border border-emerald-100 group-hover:bg-emerald-100
                                    @elseif($isActive) bg-indigo-600 text-white shadow-indigo-200 shadow-lg scale-110
                                    @else bg-white text-gray-300 border border-gray-100 group-hover:border-indigo-200
                                    @endif
                                ">
                                    @if($isCompleted)
                                        <i wire:ignore data-lucide="check" class="w-5 h-5"></i>
                                    @elseif($isLocked)
                                        <i wire:ignore data-lucide="lock" class="w-4 h-4"></i>
                                    @else
                                        <span class="font-black text-sm">{{ $tahap['urutan'] }}</span>
                                    @endif
                                </div>
                                
                                <!-- Text -->
                                <div class="flex-1 py-1">
                                    <div class="text-[9px] font-black uppercase tracking-widest mb-0.5 
                                        {{ $isActive ? 'text-indigo-500' : 'text-gray-400' }}">
                                        Tahap {{ str_pad($tahap['urutan'], 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <h4 class="text-sm font-bold leading-tight {{ $isActive ? 'text-gray-900' : 'text-gray-600' }}">
                                        {{ $tahap['nama'] }}
                                    </h4>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Active Phase Content -->
        @if($this->activeTahap)
            <div class="flex-1 w-full bg-white rounded-[2.5rem] border border-gray-100 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col min-h-[600px] animate-in slide-in-from-right-8 duration-500">
                
                <!-- Content Header & Tabs -->
                <div class="border-b border-gray-50 bg-gray-50/30">
                    <div class="p-8 pb-6 border-b border-gray-50">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-indigo-100/50">
                            Fase {{ $this->activeTahap['urutan'] }} Aktif
                        </div>
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ $this->activeTahap['nama'] }}</h2>
                    </div>
                    
                    <div class="flex items-center gap-2 px-8 py-4 overflow-x-auto">
                        <button wire:click="setTab('materi')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'materi' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                            <i wire:ignore data-lucide="book-open" class="w-3.5 h-3.5 inline mr-1"></i> Materi & Instuksi
                        </button>
                        <button wire:click="setTab('tugas')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'tugas' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                            <i wire:ignore data-lucide="paperclip" class="w-3.5 h-3.5 inline mr-1"></i> Tugas
                        </button>
                        <button wire:click="setTab('diskusi')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'diskusi' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                            <i wire:ignore data-lucide="message-squircle" class="w-3.5 h-3.5 inline mr-1"></i> Diskusi Terisolasi
                        </button>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="flex-1 p-8 bg-[#fbfcff] relative">
                    
                    <!-- Decorative Background -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.02] pointer-events-none">
                        <i wire:ignore data-lucide="workflow" class="w-96 h-96"></i>
                    </div>

                    <div class="relative z-10 w-full h-full flex flex-col">
                        
                        @if($activeTab === 'materi')
                            <div class="flex-1 animate-in fade-in duration-300">
                                <!-- Activities Section -->
                                @if(!empty($this->activeTahap['kegiatan']))
                                    <div class="mb-8">
                                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Aktivitas & Kegiatan</h4>
                                        <div class="space-y-3">
                                            @foreach($this->activeTahap['kegiatan'] as $keg)
                                                <div class="flex items-center gap-3 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                                    <div class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></div>
                                                    <span class="text-sm font-medium text-gray-700 leading-relaxed">{{ $keg }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6">File Referensi</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Dummy Material -->
                                    <div class="p-4 bg-white border border-gray-100 rounded-2xl flex gap-4 hover:border-indigo-200 hover:shadow-md transition-all group">
                                        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                            <i wire:ignore data-lucide="file-text" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Modul Pengantar.pdf</h5>
                                            <p class="text-[10px] text-gray-400 mt-1">1.2 MB &bull; Dibaca 14 kali</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($activeTab === 'tugas')
                            <div class="flex-1 animate-in fade-in duration-300">
                                <div class="text-center py-20 px-4 bg-white rounded-3xl border border-dashed border-gray-200">
                                    <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                                        <i wire:ignore data-lucide="clipboard-list" class="w-8 h-8 text-indigo-400"></i>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-900 mb-2">Tugas Mandiri</h4>
                                    <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">Silakan baca instruksi pada materi dan kumpulkan laporan analisa Anda terkait tahap ini sebelum melanjutkan.</p>
                                    @if(!$this->isReadonly)
                                        <button class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Mulai Mengerjakan</button>
                                    @else
                                        <div class="px-6 py-3 bg-gray-100 text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-gray-200 inline-block">
                                            <i wire:ignore data-lucide="lock" class="w-3.5 h-3.5 inline mr-1"></i> Penugasan Terkunci
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($activeTab === 'diskusi')
                            <div class="flex-1 flex flex-col h-full min-h-[400px] bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden animate-in fade-in duration-300">
                                <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                                    <span class="text-[11px] font-black text-gray-500 uppercase tracking-widest"><i wire:ignore data-lucide="lock" class="w-3 h-3 inline mr-1"></i> Ruang Diskusi Terisolasi</span>
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[9px] font-black rounded-full uppercase tracking-widest">Tahap {{ $this->activeTahap['urutan'] }}</span>
                                </div>
                                <div class="flex-1 p-6 overflow-y-auto bg-[#fafbfc] space-y-6">
                                    <!-- Dummy Chat Bubbles -->
                                    <div class="flex gap-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">AD</div>
                                        <div>
                                            <div class="flex items-baseline gap-2 mb-1">
                                                <span class="text-xs font-bold text-gray-900">Dr. Dosen</span>
                                                <span class="text-[9px] text-gray-400 font-medium">10:30 AM</span>
                                            </div>
                                            <div class="p-3 bg-white border border-gray-100 rounded-2xl rounded-tl-sm text-sm text-gray-600 shadow-sm">
                                                Apakah ada kesulitan dalam memahami masalah di tahap ini? Diskusi hanya seputar materi orientasi ya.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-4 flex-row-reverse">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">MH</div>
                                        <div class="text-right">
                                            <div class="flex items-baseline justify-end gap-2 mb-1">
                                                <span class="text-[9px] text-gray-400 font-medium">10:35 AM</span>
                                                <span class="text-xs font-bold text-gray-900">Anda</span>
                                            </div>
                                            <div class="p-3 bg-indigo-600 text-white rounded-2xl rounded-tr-sm text-sm shadow-md">
                                                Izin bertanya pak, untuk batasan masalahnya apakah kita fokus pada satu sub-sistem saja?
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-white border-t border-gray-50">
                                    @if(!$this->isReadonly)
                                        <div class="flex gap-2 relative">
                                            <input type="text" class="flex-1 bg-gray-50 border border-gray-100 rounded-2xl pl-4 pr-12 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Ketik pesan diskusi...">
                                            <button class="absolute right-2 top-1.5 w-9 h-9 flex items-center justify-center bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors"><i wire:ignore data-lucide="send" class="w-4 h-4"></i></button>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 p-3 rounded-2xl text-center text-gray-400 text-[10px] font-black uppercase tracking-widest border border-dashed border-gray-200">
                                            Diskusi Ditutup (Mode Arsip)
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Footer CTA -->
                <div class="p-8 border-t border-gray-50 flex justify-between items-center bg-white relative z-20">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest hidden md:block">{{ $this->isReadonly ? 'Sesi ini telah berakhir (Arsip)' : 'Pastikan Anda telah memeriksa materi dan tugas' }}</p>
                    @if(!$this->isReadonly)
                        <button wire:click="markAsCompleted" class="w-full md:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2 group">
                            @if($this->activeTahap['status'] === 'completed')
                                Tahap Sudah Selesai <i wire:ignore data-lucide="check" class="w-4 h-4"></i>
                            @else
                                Tandai Tahap Selesai <i wire:ignore data-lucide="check-circle" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                            @endif
                        </button>
                    @else
                        <div class="w-full md:w-auto px-8 py-4 bg-gray-100 text-gray-400 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] border border-gray-200 flex items-center justify-center gap-2">
                             Selesai (Read-Only) <i wire:ignore data-lucide="archive" class="w-4 h-4"></i>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
