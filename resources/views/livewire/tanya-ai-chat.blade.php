<div x-data="{ 
    scrollToBottom() { 
        this.$nextTick(() => {
            const container = this.$refs.chatContainer;
            if (container) container.scrollTop = container.scrollHeight;
        });
    }
}" 
x-init="
    $watch('$wire.messages', () => scrollToBottom());
    window.addEventListener('message-received', () => scrollToBottom());
    scrollToBottom();
"
class="p-0 md:p-0 w-full h-screen md:h-[calc(100vh-8rem)] flex flex-col">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">AI Assistant</h1>
            <p class="text-sm text-gray-400 font-medium">Asisten cerdas berbasis Gemini 3 Flash</p>
        </div>
        
        <button wire:click="clearChat" class="px-6 py-2.5 bg-gray-100 hover:bg-rose-50 hover:text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-400 transition-all flex items-center gap-2">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            Hapus Percakapan
        </button>
    </div>

    <!-- Main Container (Integrated) -->
    <div class="flex flex-col flex-1 overflow-hidden relative">
        @if($currentTab === 'chat')
            <!-- Chat Box -->
            <div 
                x-ref="chatContainer"
                class="flex-1 overflow-y-auto h-[500px] p-4 md:p-8 space-y-8 bg-transparent scroll-smooth"
            >
                @foreach($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }} max-w-[80%]">
                            <div class="px-6 py-4 rounded-[2rem] {{ $message['role'] === 'user' ? 'bg-indigo-600 text-white rounded-tr-none shadow-xl shadow-indigo-100' : 'bg-white text-gray-800 rounded-tl-none border border-gray-100 shadow-sm' }}">
                                <div class="text-[13px] leading-relaxed prose prose-sm max-w-none {{ $message['role'] === 'user' ? 'prose-invert' : '' }}">
                                    {!! nl2br(e($message['content'])) !!}
                                </div>
                            </div>
                            @if(isset($message['timestamp']))
                                <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest mt-2 px-2">{{ $message['timestamp'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div wire:loading wire:target="sendMessage" class="flex justify-start">
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] rounded-tl-none px-5 py-4 flex gap-1.5 shadow-sm">
                            <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></span>
                        </div>
                    </div>
            </div>

            <!-- Input Area -->
            <div class="px-4 md:px-8 py-4 bg-transparent shrink-0">
                <form wire:submit.prevent="sendMessage" class="relative max-w-5xl mx-auto">
                    <div class="relative flex items-center bg-white rounded-[2.5rem] border border-gray-200 pl-6 pr-2 shadow-xl shadow-gray-200/20 focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 transition-all duration-300">
                        <div class="text-gray-400">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <textarea 
                            wire:model="userInput"
                            placeholder="Tanyakan materi atau jadwal kuliah..."
                            class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-medium py-3 px-4 resize-none overflow-hidden h-auto min-h-[50px] max-h-[200px]"
                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                            @keydown.enter.prevent="if(!$event.shiftKey) $wire.sendMessage()"
                        ></textarea>
                        
                        <button 
                            type="submit" 
                            class="w-12 h-12 flex items-center justify-center bg-indigo-600 text-white rounded-full hover:bg-indigo-700 hover:shadow-lg active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed group shrink-0"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                        >
                            <i data-lucide="send" wire:loading.remove wire:target="sendMessage" class="w-5 h-5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                            <i data-lucide="loader-2" wire:loading wire:target="sendMessage" class="w-5 h-5 animate-spin"></i>
                        </button>
                    </div>
                </form>
                <div class="flex items-center justify-center gap-4 mt-6">
                    <div class="h-[1px] w-12 bg-gray-100"></div>
                    <p class="text-[9px] text-gray-300 font-bold uppercase tracking-[0.3em]">LMS AI Core v2.0 • Gemini 3 Flash</p>
                    <div class="h-[1px] w-12 bg-gray-100"></div>
                </div>
            </div>
        @endif
    </div>
    </div>

    <script>
        // Lucide is now handled globally in app.js
    </script>
