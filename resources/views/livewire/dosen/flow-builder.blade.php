<div class="p-0 md:p-4 max-w-6xl mx-auto min-h-screen bg-[#fafbfc]">
    
    <!-- Top Nav / Back -->
    <div class="mb-8">
        <a href="{{ route('dosen.classes') }}" class="inline-flex items-center text-[11px] font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors">
            <i wire:ignore data-lucide="arrow-left" class="w-3.5 h-3.5 mr-2"></i> Kembali ke Kelas
        </a>
    </div>

    <!-- Header -->
    <div class="mb-10">
        @if($this->isReadonly)
            <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 p-4 rounded-3xl text-amber-800 shadow-sm animate-pulse">
                <div class="bg-amber-100 p-2.5 rounded-2xl">
                    <i wire:ignore data-lucide="archive" class="w-6 h-6 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-black text-sm uppercase tracking-wider">Arsip / Read-Only</h4>
                    <p class="text-[11px] opacity-80 font-bold uppercase tracking-tight">Alur pembelajaran ini berasal dari periode akademik yang sudah tidak aktif. Perubahan tidak diizinkan.</p>
                </div>
            </div>
        @endif
        <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ $this->isReadonly ? 'Detail Alur Pembelajaran' : ($pertemuanId ? 'Edit Alur Pembelajaran' : 'Buat Pertemuan Baru') }}</h1>
        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">{{ $this->isReadonly ? 'Tampilan Arsip - Tidak Dapat Diubah' : 'Konfigurasi Alur Pembelajaran Kelas Anda' }}</p>
    </div>

    @if (session()->has('success_message'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 text-sm font-semibold animate-in fade-in slide-in-from-top-4">
            <i wire:ignore data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
            {{ session('success_message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3 text-rose-700 text-sm font-semibold animate-in fade-in slide-in-from-top-4">
            <i wire:ignore data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shadow-sm"></i>
            {{ session('error') }}
        </div>
    @endif


    <div class="grid grid-cols-1 gap-8">
        
        <!-- Basic Info Section -->
        <div class="p-4 bg-white/70 backdrop-blur-sm rounded-[2.5rem] border border-gray-100 shadow-[0_20px_40px_-12px_rgba(0,0,0,0.03)]">
            <h3 class="flex items-center gap-3 text-lg font-black text-gray-900 mb-6">
                <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500 border border-gray-100">1</div>
                Informasi Dasar
            </h3>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Judul / Topik Pertemuan</label>
                    <input type="text" wire:model="pertemuanKe" {{ $this->isReadonly ? 'disabled' : '' }} class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all {{ $this->isReadonly ? 'opacity-70' : '' }}" placeholder="Misal: Pertemuan 1 - Pengenalan PBL">
                </div>
            </div>
        </div>

        <!-- Syntax Builder Section -->
        <div class="p-4 bg-white/70 backdrop-blur-sm rounded-[2.5rem] border border-gray-100 shadow-[0_20px_40px_-12px_rgba(0,0,0,0.03)] border-l-4 border-l-indigo-600 relative overflow-hidden">
            <!-- decorative bg -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/50 rounded-bl-full -z-10 pointer-events-none mix-blend-multiply"></div>

            <div class="flex justify-between items-start mb-6">
                <h3 class="flex items-center gap-3 text-xl font-black text-gray-900">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                        <i wire:ignore data-lucide="puzzle" class="w-4 h-4"></i>
                    </div>
                    Sintaks Pembelajaran
                </h3>
            </div>
            
            <div class="mb-8 flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Model Pembelajaran</label>
                    <select wire:model.live="modelPembelajaran" 
                        {{ $this->isReadonly ? 'disabled' : 'wire:confirm="Mengubah model pembelajaran akan meriset (MENGHAPUS) semua tahapan dan tugas yang sudah Anda buat. Apakah Anda yakin ingin melanjutkan?"' }}
                        class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-black text-indigo-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm {{ $this->isReadonly ? 'opacity-70' : '' }}">
                        <option value="PBL">Problem Based Learning (PBL)</option>
                        <option value="PjBL">Project Based Learning (PjBL)</option>
                        <option value="DISCOVERY">Discovery Learning</option>
                        <option value="CUSTOM">✨ CUSTOM — Buat Sendiri</option>
                    </select>
                </div>

                @if($modelPembelajaran === 'CUSTOM')
                    <div class="flex-1 animate-in slide-in-from-left-4 duration-300">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Sintaks Kustom</label>
                        <input type="text" wire:model.live="customModelName" 
                            class="w-full bg-indigo-50/30 border-indigo-100 rounded-2xl p-4 text-sm font-black text-indigo-700 placeholder:text-indigo-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                            placeholder="Misal: Workshop, Seminar, Bedah Buku...">
                    </div>
                @endif
            </div>

            <div class="mt-8 animate-in slide-in-from-top-4 duration-300">
                <div class="mb-6">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Tahapan Sintaks Pelatihan</h4>
                    <p class="text-[11px] text-gray-400 font-medium tracking-tight">Sesuaikan tahapan berikut untuk mengoptimalkan alur pembelajaran kelas Anda.</p>
                </div>

                <div class="space-y-4 mb-6">
                    @foreach($tahapan as $index => $tahap)
                        <div wire:key="tahap-{{ $tahap['id'] }}" class="group relative flex flex-col md:flex-row gap-4 p-4 rounded-2xl border border-gray-100 bg-white hover:border-indigo-200 hover:shadow-md transition-all">
                            
                            <!-- Left Content -->
                            <div class="flex flex-col flex-1 gap-2 min-w-0 w-full">
                                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full">
                                    <!-- Badges & Handle -->
                                    <div class="flex items-center gap-3 shrink-0">
                                        @if(!$this->isReadonly)
                                            <div class="cursor-grab text-gray-300 hover:text-indigo-600 transition-colors">
                                                <i wire:ignore data-lucide="grip-vertical" class="w-5 h-5"></i>
                                            </div>
                                        @endif
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 font-black text-xs flex items-center justify-center border border-gray-100 shrink-0">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </div>
                                        
                                        <!-- Status Pills (Inline) -->
                                        <div class="flex gap-1 shrink-0">
                                            @if($tahap['has_materi'])
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-lg bg-blue-100 text-[8px] font-black text-blue-700 uppercase tracking-widest border border-blue-200 shadow-sm" title="Materi Aktif">MTR</span>
                                            @endif
                                            @if($tahap['has_tugas'])
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-lg bg-amber-100 text-[8px] font-black text-amber-700 uppercase tracking-widest border border-amber-200 shadow-sm" title="Tugas Aktif">TGS</span>
                                            @endif
                                            @if($tahap['has_diskusi'])
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-lg bg-emerald-100 text-[8px] font-black text-emerald-700 uppercase tracking-widest border border-emerald-200 shadow-sm" title="Diskusi Aktif">DSK</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Main Stage Input -->
                                    <input type="text" wire:model="tahapan.{{ $index }}.nama" {{ $this->isReadonly ? 'disabled' : '' }} class="flex-1 min-w-0 w-full bg-transparent border-0 border-b border-gray-100 focus:border-indigo-400 focus:ring-0 text-sm font-bold text-gray-800 placeholder-gray-300 p-0 py-1 transition-colors {{ $this->isReadonly ? 'opacity-70' : '' }}" placeholder="Ketik nama tahapan...">
                                </div>

                                <!-- Activities (Kegiatan) -->
                                <div class="md:ml-12 mt-2 space-y-2 w-full">
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Aktivitas / Kegiatan</label>
                                    @foreach($tahap['kegiatan'] ?? [] as $kegIndex => $keg)
                                        <div wire:key="keg-{{ $index }}-{{ $kegIndex }}" class="flex items-center gap-2 group/keg w-full">
                                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0 hidden md:block"></div>
                                            <input type="text" wire:model="tahapan.{{ $index }}.kegiatan.{{ $kegIndex }}" {{ $this->isReadonly ? 'disabled' : '' }}
                                                class="flex-1 min-w-0 w-full bg-gray-50/50 border-transparent rounded-xl px-3 py-1.5 text-[13px] font-medium text-gray-600 focus:bg-white focus:border-indigo-100 focus:ring-2 focus:ring-indigo-500/5 transition-all outline-none {{ $this->isReadonly ? 'opacity-70' : '' }}" 
                                                placeholder="Tulis aktivitas yang akan dikerjakan...">
                                            @if(!$this->isReadonly)
                                                <button type="button" wire:click="removeKegiatan({{ $index }}, {{ $kegIndex }})" class="opacity-100 md:opacity-0 group-hover/keg:opacity-100 text-gray-400 hover:text-rose-500 transition-all p-1 shrink-0">
                                                    <i wire:ignore data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if(!$this->isReadonly)
                                        <button type="button" wire:click="addKegiatan({{ $index }})" class="inline-flex items-center text-[10px] font-bold text-indigo-500 hover:text-indigo-700 mt-1 transition-colors">
                                            <i wire:ignore data-lucide="plus" class="w-3 h-3 mr-1"></i> Tambah Aktivitas
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-start md:justify-end gap-2 pt-3 mt-1 md:pt-0 md:mt-0 border-t md:border-t-0 md:pl-4 md:border-l border-gray-100 shrink-0 flex-wrap">
                                <button type="button" wire:click="openMateriModal({{ $index }})" title="Atur Materi" class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-all {{ $tahap['has_materi'] ? 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100' : 'bg-gray-50 text-gray-400 border border-transparent hover:bg-gray-100' }}">
                                    <i wire:ignore data-lucide="book-open" class="w-4 h-4"></i>
                                    @if($tahap['has_materi']) <span class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 border-2 border-white rounded-full"></span> @endif
                                </button>
                                <button type="button" wire:click="openTugasModal({{ $index }})" title="Atur Tugas" class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-all {{ $tahap['has_tugas'] ? 'bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100' : 'bg-gray-50 text-gray-400 border border-transparent hover:bg-gray-100' }}">
                                    <i wire:ignore data-lucide="clipboard-list" class="w-4 h-4"></i>
                                    @if($tahap['has_tugas']) <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 border-2 border-white rounded-full"></span> @endif
                                </button>
                                @if(!$this->isReadonly)
                                    <button type="button" wire:click="toggleDiskusi({{ $index }})" title="Aktifkan/Matikan Ruang Diskusi" class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-all {{ $tahap['has_diskusi'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-50 text-gray-400 border border-transparent hover:bg-gray-100' }}">
                                        <i wire:ignore data-lucide="message-circle" class="w-4 h-4"></i>
                                        @if($tahap['has_diskusi']) <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span> @endif
                                    </button>
                                    <button type="button" wire:click="removeTahapan({{ $index }})" title="Hapus Tahapan" class="w-10 h-10 rounded-xl flex items-center justify-center bg-white text-gray-300 hover:text-rose-600 hover:bg-rose-50 transition-all ml-auto md:ml-2 border border-gray-100 md:border-transparent">
                                        <i wire:ignore data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                            
                        </div>
                    @endforeach
                </div>

                @if(!$this->isReadonly)
                    <button type="button" wire:click="addTahapan" class="group flex items-center gap-2 px-5 py-3 rounded-xl border border-dashed border-gray-300 text-gray-500 text-[11px] font-black uppercase tracking-widest hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all w-full justify-center">
                        <i wire:ignore data-lucide="plus-circle" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                        Tambah Tahapan Baru
                    </button>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4 mt-4">
            <a href="{{ route('dosen.classes') }}" class="px-8 py-4 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-colors">
                Batal
            </a>
            @if(!$this->isReadonly)
                <button wire:click="saveFlow" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-[0.98] transition-all flex items-center gap-2 group">
                    Simpan & Lanjutkan
                    <i wire:ignore data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </button>
            @endif
        </div>
    </div>

    <!-- Materi Modal Fullscreen -->
    @if($isMateriModalOpen && $activeStageIndex !== null)
    <div class="fixed inset-0 z-50 flex flex-col bg-[#fafbfc] animate-in slide-in-from-bottom-4 duration-300">
        <!-- Top bar -->
        <div class="bg-white border-b border-gray-200 px-4 md:px-8 py-3 md:py-0 md:h-24 flex flex-col md:flex-row md:items-center justify-between shrink-0 shadow-sm gap-3">
            <div class="flex items-center gap-3 md:gap-5">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i wire:ignore data-lucide="book-open" class="w-5 h-5 md:w-7 md:h-7"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base md:text-2xl font-black text-gray-900 tracking-tight truncate">Konten Materi</h3>
                    <p class="text-[10px] md:text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5 truncate">Tahap: <span class="text-indigo-600">{{ $tahapan[$activeStageIndex]['nama'] ?: 'Tahap Baru' }}</span></p>
                </div>
                <button type="button" wire:click="closeMateriModal" class="md:hidden ml-auto w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-full shrink-0">
                    <i wire:ignore data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                @if(!$this->isReadonly)
                    <button type="button" wire:click="removeMateri" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-3 md:px-5 py-2 md:py-3 rounded-xl text-xs md:text-sm font-bold transition-colors">
                        Hapus Materi
                    </button>
                    <button type="button" wire:click="closeMateriModal" class="px-4 md:px-8 py-2.5 md:py-4 rounded-xl md:rounded-2xl bg-indigo-600 text-white text-[10px] md:text-[11px] font-black tracking-widest uppercase hover:bg-indigo-700 shadow-lg transition-all flex items-center gap-2">
                        Simpan <i wire:ignore data-lucide="check-circle" class="w-4 h-4"></i>
                    </button>
                @else
                    <button type="button" wire:click="closeMateriModal" class="px-4 md:px-8 py-2.5 md:py-4 rounded-xl md:rounded-2xl bg-gray-200 text-gray-700 text-[10px] md:text-[11px] font-black tracking-widest uppercase hover:bg-gray-300 transition-all flex items-center gap-2">
                        Tutup <i wire:ignore data-lucide="x-circle" class="w-4 h-4"></i>
                    </button>
                @endif
                <button type="button" wire:click="closeMateriModal" class="hidden md:flex ml-2 w-10 h-10 items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-full transition-colors" title="Batal / Tutup">
                    <i wire:ignore data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-3 md:p-8">
            <div class="max-w-4xl mx-auto space-y-6 md:space-y-8 bg-white p-4 md:p-10 rounded-2xl md:rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)]">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-3 pl-1">Judul Materi Pembelajaran</label>
                    <input type="text" wire:model="tahapan.{{ $activeStageIndex }}.materi.judul" {{ $this->isReadonly ? 'disabled' : '' }} class="w-full bg-gray-50/50 border-gray-100 rounded-2xl text-lg font-bold text-gray-800 placeholder:text-gray-300 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 p-5 transition-all {{ $this->isReadonly ? 'opacity-70' : '' }}" placeholder="Ketikkan Judul Materi yang Menarik...">
                </div>
                <div x-data="tinyEditor('tahapan.{{ $activeStageIndex }}.materi.isi', {{ $this->isReadonly ? 'true' : 'false' }})" wire:ignore>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-3 pl-1">Isi / Ringkasan Materi</label>
                    <textarea x-ref="textarea" rows="12" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl text-base text-gray-700 placeholder:text-gray-300 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 p-6 transition-all" placeholder="Tuliskan atau *paste* deskripsi materi, tautan Google Drive, link YouTube, instruksi bacaan, dll..."></textarea>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tugas / Soal Modal (Full Structure: MasterSoal > MainSoal > Soal) -->
    @if($showSoalModal && $activeStageIndex !== null)
    <div class="fixed inset-0 z-50 flex flex-col bg-[#fafbfc] animate-in slide-in-from-bottom-4 duration-300">
        <!-- Top bar -->
        <div class="bg-white border-b border-gray-200 px-4 md:px-8 py-3 md:py-0 md:h-24 flex flex-col md:flex-row md:items-center justify-between shrink-0 shadow-sm gap-3">
            <div class="flex items-center gap-3 md:gap-5">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i wire:ignore data-lucide="clipboard-list" class="w-5 h-5 md:w-7 md:h-7"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base md:text-2xl font-black text-gray-900 tracking-tight truncate">Formulir Penugasan</h3>
                    <p class="text-[10px] md:text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5 truncate">Tahap: <span class="text-indigo-600">{{ $tahapan[$activeStageIndex]['nama'] ?: 'Tahap Baru' }}</span></p>
                </div>
                <button type="button" wire:click="cancelSoalModal" class="md:hidden ml-auto w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-full shrink-0">
                    <i wire:ignore data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex items-center gap-2 md:gap-4 flex-wrap">
                @if(!$this->isReadonly)
                    <button type="button" wire:click="removeTugas" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-3 md:px-5 py-2 md:py-3 rounded-xl text-xs md:text-sm font-bold transition-colors">
                        Hapus Tugas
                    </button>
                    <button type="button" wire:click="saveSoalModal" class="px-4 md:px-8 py-2.5 md:py-4 rounded-xl md:rounded-2xl bg-amber-500 text-white text-[10px] md:text-[11px] font-black tracking-widest uppercase hover:bg-amber-600 shadow-lg transition-all flex items-center gap-2">
                        Terapkan <i wire:ignore data-lucide="check-circle" class="w-4 h-4"></i>
                    </button>
                @else
                    <button type="button" wire:click="cancelSoalModal" class="px-4 md:px-8 py-2.5 md:py-4 rounded-xl md:rounded-2xl bg-gray-200 text-gray-700 text-[10px] md:text-[11px] font-black tracking-widest uppercase hover:bg-gray-300 transition-all flex items-center gap-2">
                        Tutup <i wire:ignore data-lucide="x-circle" class="w-4 h-4"></i>
                    </button>
                @endif
                <button type="button" wire:click="cancelSoalModal" class="hidden md:flex ml-2 w-10 h-10 items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-full transition-colors" title="Batal / Tutup">
                    <i wire:ignore data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-3 md:p-8">
            <div class="max-w-5xl mx-auto space-y-4 md:space-y-6 text-black">

                {{-- Master Soal Settings --}}
                <div class="bg-white p-4 md:p-8 rounded-2xl md:rounded-[2rem] border border-gray-100 shadow-sm space-y-4 md:space-y-6">
                    <h4 class="text-sm font-black text-gray-500 uppercase tracking-widest">Pengaturan Penugasan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Penugasan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="currentSoalData.title" {{ $this->isReadonly ? 'disabled' : '' }} placeholder="Misal: Kuis Pemahaman Materi" class="border border-gray-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 p-2.5 rounded-lg w-full transition text-sm {{ $this->isReadonly ? 'opacity-70' : '' }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bobot Tugas (%)</label>
                            <input type="number" wire:model="currentSoalData.bobot" {{ $this->isReadonly ? 'disabled' : '' }} placeholder="10" class="border border-gray-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 p-2.5 rounded-lg w-full transition text-sm {{ $this->isReadonly ? 'opacity-70' : '' }}" min="0" max="100" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tenggat Waktu</label>
                            <input type="datetime-local" wire:model="currentSoalData.tenggat_waktu" {{ $this->isReadonly ? 'disabled' : '' }} class="border border-gray-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 p-2.5 rounded-lg w-full transition text-sm {{ $this->isReadonly ? 'opacity-70' : '' }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Komponen Nilai Target</label>
                            <select wire:model="currentSoalData.grading_component_id" {{ $this->isReadonly ? 'disabled' : '' }} class="border border-gray-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 p-2.5 rounded-lg w-full transition text-sm {{ $this->isReadonly ? 'opacity-70' : '' }}">
                                <option value="">-- Pilih Komponen --</option>
                                @foreach($gradingComponents as $comp)
                                    <option value="{{ $comp['id'] }}">{{ $comp['name'] }} ({{ $comp['weight'] }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Opsi Tampilan & Akses</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 text-sm cursor-pointer group">
                                <input type="checkbox" wire:model="currentSoalData.is_show_master_soal" {{ $this->isReadonly ? 'disabled' : '' }} class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4" />
                                <span class="group-hover:text-amber-700 transition">Tampilkan Instruksi / Soal</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer group">
                                <input type="checkbox" wire:model="currentSoalData.is_diskusi" {{ $this->isReadonly ? 'disabled' : '' }} class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4" />
                                <span class="group-hover:text-amber-700 transition">Aktifkan Ruang Diskusi</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer group">
                                <input type="checkbox" wire:model="currentSoalData.is_show_jawaban" {{ $this->isReadonly ? 'disabled' : '' }} class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4" />
                                <span class="group-hover:text-amber-700 transition">Izinkan Mahasiswa Menjawab</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer group">
                                <input type="checkbox" wire:model.live="currentSoalData.is_show_kunci_jawaban" {{ $this->isReadonly ? 'disabled' : '' }} class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4" />
                                <span class="group-hover:text-amber-700 transition">Tetapkan Kunci Jawaban</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Main Soals (Narasi + Soal blocks) --}}
                @if($currentSoalData['is_show_master_soal'] ?? true)
                <div class="space-y-6">
                    @foreach($currentSoalData['main_soals'] ?? [] as $mIdx => $mainSoal)
                        <div class="bg-white border border-purple-200 rounded-2xl shadow-sm overflow-hidden" wire:key="ms-{{ $mIdx }}">
                            <div class="bg-purple-50 px-5 py-3 flex justify-between items-center border-b border-purple-100">
                                <h4 class="font-bold text-sm text-purple-800 flex items-center gap-2">
                                    <i wire:ignore data-lucide="file-text" class="w-4 h-4"></i>
                                    Narasi / Petunjuk Blok Soal {{ $mIdx + 1 }}
                                </h4>
                                @if(count($currentSoalData['main_soals']) > 1)
                                    <button type="button" wire:click="removeMainSoal({{ $mIdx }})" class="text-red-500 hover:bg-red-50 p-1 rounded transition">
                                        <i wire:ignore data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="p-3 md:p-5 space-y-4">
                                {{-- Narasi --}}
                                <div wire:ignore class="text-black">
                                    <x-tiny.tiny-editor dataModel="currentSoalData.main_soals.{{ $mIdx }}.narasi" placeholder="Tuliskan petunjuk umum, narasi cerita, atau deskripsi untuk pertanyaan-pertanyaan di bawah ini..." />
                                </div>

                                {{-- Soal loop --}}
                                <div class="space-y-4 pl-4 border-l-2 border-purple-100">
                                    @foreach($mainSoal['soals'] ?? [] as $sIdx => $soal)
                                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 relative group" wire:key="soal-{{ $mIdx }}-{{ $sIdx }}">
                                            @if(count($currentSoalData['main_soals'][$mIdx]['soals']) > 1)
                                                <button type="button" wire:click="removeSoalFromMain({{ $mIdx }}, {{ $sIdx }})" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i wire:ignore data-lucide="x-circle" class="w-5 h-5"></i>
                                                </button>
                                            @endif

                                            <div class="space-y-3 mb-3 pr-0 md:pr-8">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Pertanyaan {{ $sIdx + 1 }}</label>
                                                    <x-tiny.tiny-editor dataModel="currentSoalData.main_soals.{{ $mIdx }}.soals.{{ $sIdx }}.pertanyaan" placeholder="Tulis pertanyaan spesifik di sini..." />
                                                </div>
                                                <div class="flex flex-row gap-3 items-end">
                                                    <div class="flex-1">
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                                                        <select wire:model.live="currentSoalData.main_soals.{{ $mIdx }}.soals.{{ $sIdx }}.tipe_soal" {{ $this->isReadonly ? 'disabled' : '' }} class="w-full text-sm border border-gray-300 rounded-md px-2 py-2 focus:border-amber-500 {{ $this->isReadonly ? 'opacity-70' : '' }}">
                                                            <option value="pilihan_ganda">Ganda</option>
                                                            <option value="esai">Esai</option>
                                                        </select>
                                                    </div>
                                                    <div class="w-24 shrink-0">
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Bobot</label>
                                                        <input type="number" wire:model="currentSoalData.main_soals.{{ $mIdx }}.soals.{{ $sIdx }}.bobot" {{ $this->isReadonly ? 'disabled' : '' }} class="w-full text-sm border border-gray-300 rounded-md px-2 py-2 focus:border-amber-500 {{ $this->isReadonly ? 'opacity-70' : '' }}" min="1" max="100" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Pilihan Ganda options --}}
                                            @if(($soal['tipe_soal'] ?? 'esai') === 'pilihan_ganda')
                                                <div class="bg-white border border-amber-100 rounded-md p-3 mt-2">
                                                    <label class="text-xs font-bold text-amber-700 mb-2 block">Opsi Pilihan (Tandai yang benar)</label>
                                                    <div class="space-y-2">
                                                        @foreach($soal['pilihan_ganda_options'] ?? [] as $oIdx => $opt)
                                                            <div class="flex items-start gap-3 bg-white border border-gray-100 p-3 rounded-xl hover:shadow-sm transition-shadow" wire:key="opt-{{ $mIdx }}-{{ $sIdx }}-{{ $oIdx }}">
                                                                <div class="flex flex-col items-center gap-2 shrink-0 pt-2">
                                                                    <div class="flex items-center justify-center p-1.5 cursor-pointer hover:bg-amber-100 rounded-full transition" wire:click="setCorrectOption({{ $mIdx }}, {{ $sIdx }}, {{ $oIdx }})" title="Tandai sebagai Jawaban Benar">
                                                                        <div class="w-5 h-5 rounded-full border-[3px] flex items-center justify-center transition-colors {{ $opt['is_correct'] ? 'border-amber-500 bg-amber-500' : 'border-gray-300 bg-white' }}">
                                                                            @if($opt['is_correct']) <i wire:ignore data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-[10px] font-black {{ $opt['is_correct'] ? 'text-amber-600' : 'text-gray-400' }} uppercase">{{ chr(65 + $oIdx) }}</span>
                                                                </div>
                                                                <div class="flex-1 min-w-0" wire:ignore>
                                                                    <x-tiny.tiny-editor dataModel="currentSoalData.main_soals.{{ $mIdx }}.soals.{{ $sIdx }}.pilihan_ganda_options.{{ $oIdx }}.text" placeholder="Masukkan konten untuk Pilihan {{ chr(65 + $oIdx) }}..." />
                                                                </div>
                                                                <button type="button" wire:click="removeOptionFromSoal({{ $mIdx }}, {{ $sIdx }}, {{ $oIdx }})" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition shrink-0 mt-1" title="Hapus Pilihan Ini">
                                                                    <i wire:ignore data-lucide="trash-2" class="w-4 h-4"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" wire:click="addOptionToSoal({{ $mIdx }}, {{ $sIdx }})" class="text-xs text-amber-600 hover:text-amber-800 font-medium flex items-center gap-1 mt-2">
                                                        <i wire:ignore data-lucide="plus" class="w-3 h-3"></i> Tambah Opsi
                                                    </button>
                                                </div>
                                            @elseif(($soal['tipe_soal'] ?? 'esai') === 'esai' && ($currentSoalData['is_show_kunci_jawaban'] ?? false))
                                                <div class="mt-2 text-black" wire:ignore>
                                                    <label class="block text-xs font-medium text-amber-700 mb-1">Kunci Jawaban Esai</label>
                                                    <x-tiny.tiny-editor dataModel="currentSoalData.main_soals.{{ $mIdx }}.soals.{{ $sIdx }}.kunci_jawaban" placeholder="Kata kunci atau poin penilaian..." />
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    <button type="button" wire:click="addSoalToMain({{ $mIdx }})" class="w-full border-2 border-dashed border-purple-200 text-purple-600 hover:bg-purple-50 hover:border-purple-300 py-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                                        <i wire:ignore data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Pertanyaan ke Narasi Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(!$this->isReadonly)
                        <div class="flex justify-center">
                            <button type="button" wire:click="addMainSoal" class="bg-purple-100 text-purple-700 hover:bg-purple-200 px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 shadow-sm border border-purple-200">
                                <i wire:ignore data-lucide="file-plus-2" class="w-4 h-4"></i> Tambah Blok Narasi Soal Baru
                            </button>
                        </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif
</div>
