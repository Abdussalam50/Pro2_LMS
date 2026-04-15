<div x-data="{ showMobileSidebar: false }" wire:poll.5s="loadPesan" class="{{ $compact ? 'h-full' : 'h-[calc(100vh-120px)] md:h-[calc(100vh-120px)]' }} flex flex-col md:flex-row relative bg-white {{ $compact ? '' : 'rounded-2xl md:rounded-[2.5rem] border border-gray-100 shadow-sm' }} overflow-hidden animate-in fade-in duration-500">
    
    @if(!$kelasId && !$compact)
        <!-- Class Selection Screen -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 md:p-12 bg-gray-50/30">
            <div class="max-w-3xl w-full text-center space-y-6">
                <div class="w-20 h-20 bg-indigo-100 text-indigo-600 rounded-3xl flex items-center justify-center mx-auto shadow-xl shadow-indigo-100/50">
                    <i data-lucide="layout-grid" class="w-10 h-10"></i>
                </div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-black text-gray-900">Pusat Diskusi Terpadu</h2>
                    <p class="text-sm text-gray-500 font-medium">Silakan pilih kelas terlebih dahulu untuk memulai diskusi.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-8">
                    @foreach($classes as $c)
                        <button wire:click="selectKelas('{{ $c->kelas_id }}')" 
                            class="w-full p-5 bg-indigo-50/50 border border-indigo-100/50 rounded-2xl flex items-center justify-between hover:bg-white hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-100/50 transition-all group">
                            <div class="flex items-center gap-4 text-left">
                                <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-xs shadow-lg shadow-indigo-100 shrink-0">
                                    {{ substr($c->kelas, 0, 2) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-0.5 truncate">{{ $c->mataKuliah->mata_kuliah ?? 'Tanpa Mata Kuliah' }}</div>
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ $c->kelas }}</div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ $c->kode }}</div>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-indigo-600 transition-all group-hover:translate-x-1"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <!-- Mobile Backdrop -->
        <div x-show="showMobileSidebar" 
             @click="showMobileSidebar = false"
             x-transition.opacity.duration.300ms
             class="absolute inset-0 z-30 bg-gray-900/40 backdrop-blur-sm md:hidden" style="display: none;">
        </div>

        <!-- Sidebar: Rooms & Filters -->
        @if(!$compact)
        <div class="w-[85vw] md:w-80 border-r border-gray-50 flex flex-col bg-gray-50/30 flex-shrink-0 absolute md:static top-0 bottom-0 z-40 transition-all duration-300 bg-white md:bg-gray-50/30 shadow-2xl md:shadow-none"
             x-bind:style="{ left: showMobileSidebar ? '0' : '-100%' }"
             style="left: -100%;">
            <div class="p-4 md:p-6 border-b border-gray-50">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-black text-gray-900 tracking-tight">Pusat Diskusi</h3>
                        <button wire:click="loadClasses" class="flex items-center gap-1 text-[10px] font-black text-indigo-600 hover:text-indigo-700 uppercase tracking-widest w-fit group">
                            <i data-lucide="arrow-left-right" class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform"></i> Ganti Kelas
                        </button>
                    </div>
                    <button type="button" @click="showMobileSidebar = false" class="md:hidden text-gray-400 hover:text-gray-600 p-1.5 rounded-xl bg-gray-100 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <!-- Scope Switcher -->
                <div class="flex p-1 bg-white border border-gray-100 rounded-xl overflow-hidden gap-1">
                    <button wire:click="setScope('class')" class="flex-1 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all {{ $scope === 'class' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        Kelas
                    </button>
                    <button wire:click="setScope('group')" class="flex-1 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all {{ $scope === 'group' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        Kelompok
                    </button>
                    <button wire:click="setScope('lecturer')" class="flex-1 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all {{ $scope === 'lecturer' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        Dosen
                    </button>
                </div>
            </div>

            <!-- Room List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-hide">
                @forelse($rooms as $room)
                    <div x-data="{ open: @json($selectedPertemuanId === $room['pertemuan_id']) }">
                        <button @click="open = !open" 
                            class="w-full flex items-center justify-between p-3 rounded-xl transition-all {{ $selectedPertemuanId === $room['pertemuan_id'] ? 'bg-white border border-indigo-100 shadow-sm' : 'hover:bg-white/50' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-[10px]">
                                    {{ $loop->iteration }}
                                </div>
                                <span class="text-xs font-bold text-gray-700">{{ $room['judul'] }}</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-collapse class="mt-2 ml-4 space-y-1 border-l-2 border-indigo-50 pl-4">
                            <!-- Meeting Global Chat -->
                            <button wire:click="selectRoom('{{ $room['pertemuan_id'] }}', null)" 
                                class="w-full text-left px-3 py-2 rounded-lg text-xs font-medium transition-all {{ ($selectedPertemuanId === $room['pertemuan_id'] && !$tahapanSintaksId) ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-gray-500 hover:text-indigo-600' }}">
                                Umum Pertemuan
                            </button>
                            
                            <!-- Stage Specific Chats -->
                            @forelse($room['tahapan'] as $t)
                                <button wire:click="selectRoom('{{ $room['pertemuan_id'] }}', '{{ $t['id'] }}')" 
                                    class="w-full text-left px-3 py-2 rounded-lg text-xs font-medium transition-all {{ ($tahapanSintaksId === $t['id']) ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-gray-500 hover:text-indigo-600' }}">
                                    Tahap: {{ $t['nama'] }}
                                </button>
                            @empty
                                <!-- No stages -->
                            @endforelse

                            <!-- Group List for Lecturer -->
                            @if(Auth::user()->role === 'dosen' && ($scope === 'group' || $scope === 'lecturer'))
                                <div class="mt-2 pt-2 border-t border-indigo-50">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Kelompok:</p>
                                    @forelse($room['groups'] as $g)
                                        <button wire:click="selectRoom('{{ $room['pertemuan_id'] }}', '{{ $tahapanSintaksId }}', '{{ $g['id'] }}')" 
                                            class="w-full text-left px-3 py-2 rounded-lg text-[11px] font-medium transition-all {{ ($kelompokId === $g['id']) ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-gray-500 hover:text-indigo-600' }}">
                                            <i data-lucide="users-2" class="w-3 h-3 inline mr-1"></i> {{ $g['nama'] }}
                                        </button>
                                    @empty
                                        <p class="text-[9px] text-gray-400 italic">Belum ada kelompok</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center opacity-40 grayscale py-12">
                        <i data-lucide="folder-x" class="w-10 h-10 mb-3 text-gray-400"></i>
                        <p class="text-[10px] font-black uppercase tracking-widest text-center">Belum ada pertemuan/kelas</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
            @if(!$compact)
            <div class="px-4 md:px-8 py-4 md:py-5 border-b border-gray-100 bg-white/80 backdrop-blur-md flex justify-between items-center shrink-0 sticky top-0 z-20">
                <div class="flex items-center gap-3 md:gap-4">
                    <button type="button" @click="console.log('Sidebar Toggle: Dibuka'); showMobileSidebar = true" class="md:hidden mr-1 p-2 rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i data-lucide="{{ $scope === 'class' ? 'users' : ($scope === 'group' ? 'users-2' : 'user-check') }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-gray-900 leading-none">
                            {{ $scope === 'class' ? 'Diskusi Kelas' : ($scope === 'group' ? 'Diskusi Kelompok' : 'Konsultasi Dosen') }}
                        </h4>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online &bull; {{ count($pesan) }} Pesan
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Messages Area -->
            <div class="flex-1 {{ $compact ? 'p-4' : 'p-4 md:p-8' }} overflow-y-auto bg-[#fafbfc]/50 space-y-4 scrollbar-hide" id="chat-container-{{ $this->getId() }}">
                @forelse($pesan as $p)
                    <div class="flex gap-4 {{ $p['is_me'] ? 'flex-row-reverse' : '' }} animate-in slide-in-from-{{ $p['is_me'] ? 'right' : 'left' }}-4 duration-300">
                        <div class="w-10 h-10 rounded-2xl {{ $p['is_dosen'] ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-100 text-gray-600' }} flex items-center justify-center font-black text-xs shrink-0 shadow-sm">
                            {{ substr($p['user_name'], 0, 2) }}
                        </div>
                        <div class="{{ $p['is_me'] ? 'text-right' : '' }} max-w-[85%] md:max-w-[70%]">
                            <div class="flex items-baseline gap-2 mb-1.5 {{ $p['is_me'] ? 'justify-end' : '' }}">
                                <span class="text-xs font-black text-gray-900">{{ $p['user_name'] }}</span>
                                @if($p['is_dosen'])
                                    <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-600 text-[8px] font-black rounded uppercase tracking-widest">Dosen</span>
                                @endif
                                <span class="text-[9px] text-gray-400 font-bold">{{ $p['waktu'] }}</span>
                            </div>
                            <div class="p-4 rounded-3xl shadow-sm text-sm leading-relaxed {{ $p['is_me'] ? 'bg-indigo-600 text-white rounded-tr-sm' : 'bg-white border border-gray-100 text-gray-600 rounded-tl-sm' }}">
                                {!! nl2br(e($p['pesan'])) !!}
                                @if($p['lampiran'] ?? null)
                                    <div class="mt-3 pt-3 border-t {{ $p['is_me'] ? 'border-indigo-500' : 'border-gray-50' }}">
                                        <a href="{{ $p['lampiran'] }}" target="_blank" class="flex items-center gap-2 text-[10px] font-bold {{ $p['is_me'] ? 'text-indigo-100' : 'text-indigo-600' }}">
                                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Lampiran
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center opacity-30 grayscale py-12">
                        <i data-lucide="message-square" class="w-16 h-16 mb-4"></i>
                        <p class="text-xs font-black uppercase tracking-widest">Belum ada percakapan</p>
                    </div>
                @endforelse
            </div>

            <!-- Input Area -->
            <div class="{{ $compact ? 'p-3' : 'p-4 md:p-6' }} bg-white/90 backdrop-blur-md border-t border-gray-100">
                @php
                    $canChat = false;
                    if (!$this->isReadonly) {
                        if ($scope === 'class' && $selectedPertemuanId) {
                            $canChat = true;
                        } elseif (in_array($scope, ['group', 'lecturer']) && $selectedPertemuanId && $kelompokId) {
                            $canChat = true;
                        }
                    }
                @endphp
                @if($canChat)
                <form wire:submit.prevent="kirimPesan" class="flex gap-4 items-end relative">
                    <div class="flex-1 relative">
                        <textarea wire:model="pesanBaru" placeholder="Ketik pesan..." 
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all resize-none min-h-[52px] max-h-32"
                            rows="1" @keydown.enter.prevent="if(!event.shiftKey) $wire.kirimPesan()"></textarea>
                    </div>
                    <button type="submit" class="w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg hover:bg-indigo-700 active:scale-95 transition-all shrink-0">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </form>
                @else
                <div class="text-center py-2">
                    @if($this->isReadonly)
                        <div class="flex items-center justify-center gap-2 text-amber-600 animate-pulse">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest italic">
                                Diskusi Terkunci (Arsip Periode Akademik)
                            </p>
                        </div>
                    @else
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic animate-pulse">
                            {{ $scope === 'class' ? 'Pilih topik pertemuan di sidebar untuk mengirim pesan' : 'Pilih spesifikasi kelompok dan topik di sidebar untuk mengirim pesan' }}
                        </p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    @endif

    @script
    <script>
        const chatId = 'chat-container-{{ $this->getId() }}';
        function scrollToBottom() {
            const container = document.getElementById(chatId);
            if(container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }
        $wire.on('scroll-chat-bottom', () => setTimeout(scrollToBottom, 50));
        scrollToBottom();
    </script>
    @endscript
</div>
