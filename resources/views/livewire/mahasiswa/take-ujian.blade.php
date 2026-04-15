<div class="min-h-screen bg-[#f8fafc] pb-32" 
    x-data="examSecurity(@js($securitySettings))" 
    @if($securitySettings['isRestricted'])
    @contextmenu.prevent="$event.preventDefault();"
    @copy.prevent="$event.preventDefault();"
    @paste.prevent="$event.preventDefault();"
    @keydown.window="handleKeydown($event)"
    @fullscreenchange.window="checkFullscreen()"
    @visibilitychange.window="handleVisibilityChange()"
    @endif>

    {{-- Security Overlay (Strict & Materi Only) — NOT rendered for open mode --}}
    @if($securitySettings['isRestricted'])
    <div x-show="showOverlay" x-cloak class="fixed inset-0 z-[100] bg-gray-900/95 backdrop-blur-xl flex flex-col items-center justify-center p-6 text-center">
        <div class="w-24 h-24 rounded-3xl bg-rose-500/20 flex items-center justify-center text-rose-500 mb-8 border border-rose-500/30">
            <i data-lucide="shield-alert" class="w-12 h-12"></i>
        </div>
        <h2 class="text-3xl font-black text-white mb-4 tracking-tight">Mode Keamanan Ujian Aktif</h2>
        <p class="text-gray-300 max-w-lg mb-10 leading-relaxed font-medium" x-text="overlayMessage">Ujian ini memerlukan layar penuh. Anda tidak diizinkan membuka tab lain.</p>
        
        <button @click="enterFullscreen" x-show="!isFullscreen && warningCount < 3" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-500/30 flex items-center gap-3">
            <i data-lucide="maximize" class="w-5 h-5"></i> Mulai Ujian (Layar Penuh)
        </button>

        <button @click="acknowledgeWarning" x-show="isFullscreen && warningCount > 0 && warningCount < 3" class="px-8 py-4 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black uppercase tracking-widest transition-all shadow-xl shadow-rose-500/30 flex items-center gap-3">
            <i data-lucide="check" class="w-5 h-5"></i> Saya Mengerti
        </button>
    </div>
    @endif

    {{-- Violation Warning Counter — NOT rendered for open mode --}}
    @if($securitySettings['isRestricted'])
    <div x-show="warningCount > 0" x-cloak class="fixed top-24 left-1/2 -translate-x-1/2 z-50 bg-rose-600 border border-rose-500 text-white px-6 py-3 rounded-2xl shadow-xl shadow-rose-600/20 flex items-center gap-3 pointer-events-none animate-in fade-in slide-in-from-top-4">
        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        <div class="text-left">
            <div class="text-[10px] font-black uppercase tracking-widest opacity-80">Peringatan Pelanggaran</div>
            <div class="text-sm font-bold"><span x-text="warningCount"></span> kali meninggalkan ujian</div>
        </div>
    </div>
    @endif
    <!-- Top Progress Header -->
    <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 rounded-full md:px-6 py-3 md:py-4 flex items-center justify-between ">
            <div class="flex items-center gap-3 md:gap-4 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 shrink-0">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    @if($this->isReadonly)
                        <div class="mb-1 flex items-center gap-1.5 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200 text-amber-700 animate-pulse">
                            <i data-lucide="archive" class="w-2.5 h-2.5"></i>
                            <span class="text-[8px] font-black uppercase tracking-widest leading-none">Arsip Terkunci</span>
                        </div>
                    @endif
                    <h1 class="text-xs md:text-sm font-black text-gray-900 leading-none truncate">{{ $ujian->nama_ujian }}</h1>
                    <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1 truncate">{{ $ujian->mataKuliah->mata_kuliah }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 md:gap-6 shrink-0">
                <div class="text-right">
                    <p class="text-[8px] md:text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Sisa Waktu</p>
                    <div class="text-xs md:text-sm font-black tracking-tight" x-data="{ 
                        timeLeft: '',
                        endTime: '{{ $ujian->waktu_selesai->format('Y-m-d H:i:s') }}',
                        isUrgent: false,
                        updateTimer() {
                            const now = new Date().getTime();
                            const dist = new Date(this.endTime).getTime() - now;
                            if (dist < 0) {
                                this.timeLeft = '00:00:00';
                                this.isUrgent = true;
                                return;
                            }
                            this.isUrgent = dist < 5 * 60 * 1000; // red when < 5 min
                            const h = Math.floor(dist / (1000 * 60 * 60)).toString().padStart(2, '0');
                            const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                            const s = Math.floor((dist % (1000 * 60)) / 1000).toString().padStart(2, '0');
                            this.timeLeft = `${h}:${m}:${s}`;
                        }
                    }" x-init="updateTimer(); setInterval(() => updateTimer(), 1000)"
                    :class="isUrgent ? 'text-rose-600 animate-pulse' : 'text-indigo-600'">
                        <span x-text="timeLeft">00:00:00</span>
                    </div>
                </div>
                @if(!$this->isReadonly)
                    <button @click="confirmSubmit()" class="px-4 md:px-6 py-2 md:py-3 bg-emerald-600 text-white text-[9px] md:text-[10px] font-black uppercase tracking-widest rounded-lg md:rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                        Kumpulkan
                    </button>
                @else
                    <button disabled class="px-4 md:px-6 py-2 md:py-3 bg-gray-200 text-gray-400 text-[9px] md:text-[10px] font-black uppercase tracking-widest rounded-lg md:rounded-xl cursor-not-allowed">
                        Arsip Terkunci
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Questions Content -->
    <div class="max-w-5xl mx-auto px-4 md:px-6 mt-6 md:mt-12 space-y-4 md:space-y-8">
        @foreach($soals as $index => $soal)
            <div wire:key="soal-{{ $soal->soal_id }}" class="bg-white rounded-3xl md:rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500" style="animation-delay: {{ $index * 50 }}ms" x-init="console.log('[Ujian] Soal {{ $index + 1 }} - ID: {{ $soal->soal_id }} - Tipe: {{ $soal->pilihan_ganda ? 'pilihan_ganda' : 'esai' }}')">
                <div class="p-4 md:p-8">
                    <div class="flex flex-col md:flex-row gap-3 md:gap-6 mb-4 md:mb-6">
                        <div class="w-7 h-7 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-gray-50 flex items-center justify-center text-[8px] md:text-[10px] font-black text-gray-400 shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-gray-800 font-bold leading-relaxed text-sm md:text-lg prose prose-sm md:prose-base max-w-none math-container">
                                {!! $soal->soal !!}
                            </div>
                        </div>
                    </div>

                    @if($soal->pilihan_ganda)
                        <div class="grid grid-cols-1 gap-2 md:gap-3 md:ml-16">
                            @foreach($soal->pilihan_ganda as $key => $value)
                                @if($value)
                                    <label class="flex items-center gap-3 md:gap-4 p-3 md:p-4 rounded-xl md:rounded-2xl border cursor-pointer transition-all duration-300 group {{ ($answers[$soal->soal_id] ?? '') === $key ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-500/10' : 'bg-gray-50/50 border-gray-100 hover:border-indigo-100 hover:bg-white' }}">
                                        <input type="radio" wire:model.live="answers.{{ $soal->soal_id }}" value="{{ $key }}" class="hidden">
                                        <div class="relative w-7 h-7 md:w-8 md:h-8 rounded-lg md:rounded-xl flex items-center justify-center font-black text-[9px] md:text-[11px] uppercase transition-all {{ ($answers[$soal->soal_id] ?? '') === $key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-gray-400 border border-gray-100 group-hover:border-indigo-200' }}">
                                            <span wire:loading.remove wire:target="answers.{{ $soal->soal_id }}">{{ $key }}</span>
                                            <div wire:loading wire:target="answers.{{ $soal->soal_id }}" class="absolute inset-0 flex items-center justify-center">
                                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-indigo-400"></i>
                                            </div>
                                        </div>
                                        <div class="text-[13px] md:text-sm font-medium transition-colors prose prose-sm max-w-none math-container {{ ($answers[$soal->soal_id] ?? '') === $key ? 'text-indigo-900' : 'text-gray-600' }}">
                                            {!! $value !!}
                                        </div>
                                        @if(($answers[$soal->soal_id] ?? '') === $key)
                                            <div wire:loading.remove wire:target="answers.{{ $soal->soal_id }}" class="ml-auto">
                                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 md:w-4 md:h-4 text-indigo-600"></i>
                                            </div>
                                        @endif
                                        <div wire:loading wire:target="answers.{{ $soal->soal_id }}" class="ml-auto">
                                            <span class="text-[8px] font-black text-indigo-300 animate-pulse uppercase tracking-tighter">Saving...</span>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="md:ml-16 mt-4">
                            <div x-data="tinyEditor('answers.{{ $soal->soal_id }}')" wire:ignore>
                                <textarea x-ref="textarea" class="w-full bg-gray-50/50 border-gray-100 rounded-xl md:rounded-2xl p-4 md:p-6 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all min-h-[120px] md:min-h-[200px]" placeholder="Tuliskan jawaban esai Anda di sini..."></textarea>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if($soals->isEmpty())
        <div class="max-w-2xl mx-auto mt-24 text-center">
            <div class="w-20 h-20 rounded-[2rem] bg-indigo-50 flex items-center justify-center text-indigo-300 mx-auto mb-6">
                <i data-lucide="file-warning" class="w-10 h-10"></i>
            </div>
            <h2 class="text-xl font-black text-gray-900">Belum ada soal</h2>
            <p class="text-sm text-gray-400 mt-2">Ujian ini belum memiliki soal. Silakan hubungi dosen pengampu.</p>
        </div>
    @endif

    <!-- Materi Modal for Materi Only Mode -->
    @if($ujian->mode_batasan === 'materi_only')
        <!-- Floating FAB -->
        <button @click="showMateri = true" x-show="!showMateri" class="fixed bottom-8 right-8 z-40 bg-indigo-600 hover:bg-indigo-700 text-white p-5 rounded-2xl shadow-xl shadow-indigo-600/30 transition-all flex items-center gap-3 group active:scale-95">
            <i data-lucide="book-open" class="w-6 h-6"></i>
            <span class="text-[11px] font-black uppercase tracking-widest w-0 overflow-hidden group-hover:w-24 group-hover:ml-2 transition-all duration-300">Lihat Materi</span>
        </button>

        <!-- Premium Modal Materi -->
        <div 
            x-show="showMateri" 
            x-cloak 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md" @click="showMateri = false"></div>

            <!-- Modal Content -->
            <div 
                x-show="showMateri"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl overflow-hidden flex flex-col max-h-[80vh] border border-gray-100"
            >
                <!-- Close Button -->
                <button @click="showMateri = false" class="absolute top-6 right-6 w-10 h-10 bg-gray-50 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-2xl flex items-center justify-center transition-all z-10">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <!-- Header -->
                <div class="p-8 bg-indigo-50/50 border-b border-gray-50 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-indigo-100 flex items-center justify-center text-indigo-600">
                            <i data-lucide="book-open" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 tracking-tight leading-none">Materi Referensi</h2>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-[0.2em] mt-2">Akses Terbatas Sesuai Izin Dosen</p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-8 overflow-y-auto space-y-4 bg-white custom-scrollbar">
                    @forelse($ujian->materiUjians as $materi)
                        <div class="p-6 rounded-[2rem] border border-gray-50 bg-gray-50/30 hover:bg-white hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-500/5 transition-all group">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 group-hover:border-indigo-100 transition-all duration-500">
                                    <i data-lucide="file-text" class="w-6 h-6"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-black text-gray-900 text-base mb-1 group-hover:text-indigo-600 transition-colors">{{ $materi->nama_materi }}</h3>
                                    <p class="text-xs text-gray-500 mb-4 leading-relaxed font-medium line-clamp-2">{{ $materi->deskripsi }}</p>
                                    
                                    @if($materi->file_materi)
                                        <div class="flex items-center gap-3">
                                            <button @click="openViewer('{{ Storage::url($materi->file_materi) }}')" class="inline-flex items-center gap-2.5 px-6 py-2.5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 active:scale-95">
                                                Buka Materi <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Penampil Internal</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 mb-4">
                                <i data-lucide="inbox" class="w-8 h-8"></i>
                            </div>
                            <p class="text-xs font-black text-gray-300 uppercase tracking-widest italic">Tidak ada materi terlampir</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer -->
                <div class="p-6 bg-gray-50/50 border-t border-gray-50 text-center shrink-0">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Sistem Pro2Lms - Anti Cheating Engine v2.0</p>
                </div>
            </div>
        </div>

        <!-- Material Viewer Container (Full Screen Safe) -->
        <div 
            x-show="showViewer" 
            x-cloak 
            class="fixed inset-0 z-[110] bg-gray-900 flex flex-col"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-105"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-105"
        >
            <!-- Viewer Header -->
            <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="showViewer = false" class="text-gray-400 hover:text-white transition p-2 hover:bg-gray-700 rounded-xl">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </button>
                    <span class="text-xs font-black text-white uppercase tracking-widest">Materi Penampil</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">Akses Aman</span>
                </div>
            </div>
            
            <!-- Iframe Content -->
            <div class="flex-1 bg-gray-100 overflow-hidden relative">
                <template x-if="viewMateriUrl">
                    <iframe :src="viewMateriUrl" class="w-full h-full border-none shadow-inner" allow="fullscreen"></iframe>
                </template>
                <!-- Loading State inside iframe container -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none -z-10 bg-gray-50">
                    <div class="flex flex-col items-center gap-4">
                         <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                         <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Memuat Materi...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    {{-- exam-engine.js dimuat di layout.blade.php secara global --}}
    @endpush
</div>
