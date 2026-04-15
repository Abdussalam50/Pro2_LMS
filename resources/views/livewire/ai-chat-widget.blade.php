<div 
    @mousemove.window="doDrag"
    @mouseup.window="stopDrag"
    @touchmove.window="doDrag"
    @touchend.window="stopDrag"
    x-data="{ 
        open: @entangle('isOpen'),
        position: JSON.parse(localStorage.getItem('chatWidgetPos')) || { top: '50%', right: '10px', bottom: 'auto', left: 'auto', transform: 'translateY(-50%)' },
        isDragging: false,
        hasDragged: false,
        startX: 0,
        startY: 0,
        startTop: 0,
        startLeft: 0,
        startDrag(evt) {
            this.isDragging = true;
            this.hasDragged = false;
            let e = evt.type.includes('touch') ? evt.touches[0] : evt;
            this.startX = e.clientX;
            this.startY = e.clientY;
            
            const rect = this.$refs.widgetBtn.getBoundingClientRect();
            this.startTop = rect.top;
            this.startLeft = rect.left;
        },
        doDrag(evt) {
            if (!this.isDragging) return;
            let e = evt.type.includes('touch') ? evt.touches[0] : evt;
            let dx = e.clientX - this.startX;
            let dy = e.clientY - this.startY;
            
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                this.hasDragged = true;
            }
            
            if (this.hasDragged) {
                evt.preventDefault();
                let newTop = this.startTop + dy;
                let newLeft = this.startLeft + dx;
                
                // Restrict within window boundaries
                newTop = Math.max(10, Math.min(newTop, window.innerHeight - 80));
                newLeft = Math.max(10, Math.min(newLeft, window.innerWidth - 80));
                
                this.position = { top: newTop + 'px', left: newLeft + 'px', right: 'auto', bottom: 'auto', transform: 'none' };
            }
        },
        stopDrag() {
            if (this.isDragging) {
                this.isDragging = false;
                if (this.hasDragged) {
                    localStorage.setItem('chatWidgetPos', JSON.stringify(this.position));
                    // Brief delay to prevent the click listener from triggering after drag ends
                    setTimeout(() => { this.hasDragged = false; }, 100);
                }
            }
        },
        handleClick(evt) {
            if (this.hasDragged) {
                evt.preventDefault();
                evt.stopPropagation();
                return;
            }
            this.open = !this.open;
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.scrollContainer;
                if (container) container.scrollTop = container.scrollHeight;
            });
        },
        clampPosition() {
            if (this.position.left && this.position.left.endsWith('px')) {
                let left = parseInt(this.position.left);
                if (left > window.innerWidth - 80 || left < 0) {
                    this.position.left = Math.max(10, window.innerWidth - 80) + 'px';
                }
            }
            if (this.position.top && this.position.top.endsWith('px')) {
                let top = parseInt(this.position.top);
                if (top > window.innerHeight - 80 || top < 0) {
                    this.position.top = Math.max(10, window.innerHeight - 80) + 'px';
                }
            }
            localStorage.setItem('chatWidgetPos', JSON.stringify(this.position));
        }
    }"
    x-init="
        clampPosition();
        window.addEventListener('resize', () => clampPosition());
        $watch('open', value => { 
            if(value) {
                setTimeout(() => {
                    scrollToBottom();
                }, 100);
            }
        });
        $watch('$wire.messages', () => {
            scrollToBottom();
        });
    "
>
    <!-- Floating Button Container -->
    <div 
        class="chatbot-container"
        :style="`top: ${position.top}; left: ${position.left}; right: ${position.right}; bottom: ${position.bottom}; transform: ${position.transform};`"
    >
    <!-- Draggable Button -->
    <button 
        x-ref="widgetBtn"
        @mousedown="startDrag"
        @touchstart.passive="startDrag"
        @click.prevent="handleClick($event)"
        :class="isDragging ? 'scale-110 shadow-2xl cursor-grabbing' : 'hover:scale-110 cursor-grab hover:-translate-y-2 active:scale-95'"
        class="group relative w-16 h-16 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-[0_20px_50px_-12px_rgba(79,70,229,0.5)] flex items-center justify-center transition-all duration-300 overflow-hidden"
    >
        <!-- Animated Background Glow -->
        <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-500"></div>
        
        <!-- Icon Switcher -->
        <div class="relative z-10 transition-all duration-500 transform" :class="open ? 'rotate-180 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'">
            <i data-lucide="sparkles" class="w-7 h-7 text-white animate-pulse"></i>
        </div>
        <div class="absolute inset-0 flex items-center justify-center transition-all duration-500 transform scale-0 opacity-0" :class="open ? 'rotate-0 scale-100 opacity-100' : '-rotate-180 scale-0 opacity-0'">
            <i data-lucide="x" class="w-7 h-7 text-white"></i>
        </div>

        <!-- Notification Pulse -->
        <span class="absolute top-0 right-0 flex h-3 w-3 -mt-1 -mr-1" x-show="!open">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
        </span>
    </button>

    </div>

    <div 
        class="chatbot-window shadow-2xl"
        :class="open ? 'is-open' : ''"
        x-show="open"
        @click.away="open = false"
    >
        <!-- Header -->
        <div class="p-6 bg-indigo-600 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md">
                    <i data-lucide="bot" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-tight">Chatbot Materi</h3>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-indigo-100">RAG Mode Active</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                @if(auth()->user()->role === 'dosen')
                <button wire:click="toggleSettings" class="p-2 hover:bg-white/10 rounded-lg transition-colors relative group">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </button>
                @endif
                <button @click="open = false" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        @if($showSettings && auth()->user()->role === 'dosen')
            <!-- Knowledge Base Management (Dosen Only) -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="mb-4 flex items-center justify-between">
                    <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Manage Knowledge Base</h5>
                    <button wire:click="toggleSettings" class="text-[9px] font-black text-indigo-600 uppercase">Back</button>
                </div>


                <!-- Upload -->
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4">
                    <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all">
                        <input type="file" wire:model="kbFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="text-indigo-500 mb-1">
                            <i data-lucide="upload-cloud" class="w-6 h-6 mx-auto"></i>
                        </div>
                        <p class="text-[10px] font-black text-gray-700 uppercase tracking-wider">Sync New Document</p>
                    </div>
                    @if($kbFile)
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between gap-3 p-2 bg-indigo-50 rounded-lg">
                                <span class="text-[9px] font-bold truncate max-w-[150px]">{{ $kbFile->getClientOriginalName() }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-1">Category</label>
                                <input type="text" wire:model="kbCategory" class="w-full bg-gray-50 border-gray-100 rounded-xl px-3 py-2 text-[10px] font-bold focus:ring-2 focus:ring-indigo-500/20" placeholder="e.g. Matematika, Fisika...">
                            </div>
                            <button wire:click="uploadKbFile" wire:loading.attr="disabled" class="w-full bg-indigo-600 text-white px-3 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest disabled:opacity-50 shadow-lg shadow-indigo-100 active:scale-95 transition-all">
                                <span wire:loading.remove wire:target="uploadKbFile">Synchronize to RAG</span>
                                <span wire:loading wire:target="uploadKbFile" class="flex items-center justify-center gap-2">
                                    <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i> Processing...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- List -->
                <div class="space-y-2">
                    @if(count($uploadedFiles) > 0)
                        @foreach($uploadedFiles as $file)
                            <div wire:key="rag-file-{{ $loop->index }}" class="bg-white p-3 rounded-xl border border-gray-100 flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-700 truncate max-w-[130px]">{{ $file['filename'] }}</p>
                                        <p class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">{{ $file['category'] ?? 'General' }} • {{ $file['total_chunks'] ?? 0 }} Chunks</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ $this->getPreviewUrl($file['filename']) }}" target="_blank" class="text-indigo-400 hover:text-indigo-600 p-1 transition-colors">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <button 
                                        wire:click="deleteKbFile('{{ $file['filename'] }}')" 
                                        wire:confirm="Hapus dokumen ini dari RAG Engine?"
                                        class="text-rose-400 hover:text-rose-600 p-1 transition-colors"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-[10px] text-center text-gray-400 font-bold uppercase py-10 italic">No documents indexed</p>
                    @endif
                </div>
            </div>
        @else
            <!-- Messages Area -->
            <div 
                id="widget-chat-container"
                class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/30 scroll-smooth"
                x-ref="scrollContainer"
            >
                @foreach($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="max-w-[85%] px-4 py-3 rounded-2xl {{ $message['role'] === 'user' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none border border-gray-100 shadow-sm' }}">
                            <p class="text-xs leading-relaxed prose prose-sm {{ $message['role'] === 'user' ? 'prose-invert' : '' }}">{!! nl2br(e($message['content'])) !!}</p>
                            <span class="block text-[8px] mt-2 font-bold opacity-30 text-right uppercase tracking-[0.1em]">{{ $message['timestamp'] }}</span>
                        </div>
                    </div>
                @endforeach

                <div wire:loading wire:target="sendMessage" class="flex justify-start">
                        <div class="bg-white border border-gray-100 rounded-xl rounded-tl-none px-4 py-2.5 flex gap-1 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></span>
                        </div>
                    </div>
            </div>

            <!-- Input Area -->
            <div class="p-5 bg-white border-t border-gray-100 shrink-0">
                <form wire:submit.prevent="sendMessage" class="relative">
                    <div class="flex items-center bg-gray-50 rounded-2xl border border-gray-100 p-1.5 pl-4 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 transition-all">
                        <input 
                            type="text" 
                            wire:model="userInput"
                            placeholder="Tanyakan materi..."
                            class="flex-1 bg-transparent border-none focus:ring-0 text-xs font-semibold py-2.5 placeholder:text-gray-400"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                        >
                        <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage" class="w-10 h-10 flex items-center justify-center bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 shrink-0">
                            <i data-lucide="send" wire:loading.remove wire:target="sendMessage" class="w-4 h-4"></i>
                            <i data-lucide="loader-2" wire:loading wire:target="sendMessage" class="w-4 h-4 animate-spin"></i>
                        </button>
                    </div>
                </form>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <span class="w-1 h-1 bg-green-400 rounded-full"></span>
                    <p class="text-[7px] text-gray-300 font-black uppercase tracking-[0.2em]">RAG Content Engine</p>
                    <span class="w-1 h-1 bg-green-400 rounded-full"></span>
                </div>
            </div>
        @endif
    </div>
    <style>
        /* Icon Container */
        .chatbot-container {
            position: fixed;
            z-index: 1000;
            touch-action: none; /* Prevents scroll interference when dragging */
        }

        /* Jendela Chat */
        .chatbot-window {
            position: fixed;
            z-index: 1001; /* Di atas container icon */
            top: 50%;
            background: white;
            border: 1px solid #f3f4f6;
            box-shadow: 0 40px 100px -24px rgba(0,0,0,0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.4s ease;
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .chatbot-window.is-open {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        /* Mobile Styles */
        @media (max-width: 767px) {
            .chatbot-window {
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%) scale(0.95);
                width: calc(100vw - 32px);
                height: 500px;
                max-height: 80vh;
                border-radius: 1.5rem;
            }
            .chatbot-window.is-open {
                transform: translate(-50%, -50%) scale(1);
            }
        }

        /* Desktop Styles */
        @media (min-width: 768px) {
            .chatbot-window {
                right: 90px;
                top: 50%;
                transform: translateY(-50%) scale(0.95);
                width: 400px;
                height: 600px;
                max-height: calc(100vh - 40px);
                border-radius: 1rem;
            }
            .chatbot-window.is-open {
                transform: translateY(-50%) scale(1);
            }
        }
    </style>

    <script>
        // Lucide handled globally
    </script>
</div>
