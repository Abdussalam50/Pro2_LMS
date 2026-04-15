<div wire:poll.5s="loadPesan" class="{{ $compact ? 'h-[400px]' : ($pertemuanData ? 'h-full' : 'h-[calc(100vh-80px)]') }} flex flex-col bg-gray-50 flex-1 overflow-hidden rounded-xl border border-gray-100 shadow-inner">
    @if(!$compact)
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm z-20">
        <div class="flex items-center gap-4">
            <a href="{{ Auth::user()->role === 'dosen' ? '/dosen/classes/' . $kelasId : '/mahasiswa/classes/' . $kelasId }}" 
               class="p-2 hover:bg-gray-100 rounded-full transition text-gray-400 hover:text-indigo-600">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="message-circle" class="w-6 h-6 text-indigo-600"></i>
                    Diskusi Kelas
                </h1>
                <p class="text-xs text-gray-500 font-medium tracking-wide uppercase">
                    {{ $pertemuanData['pertemuan'] ?? 'Pertemuan' }} • {{ $pertemuanData['kelas']['kelas'] ?? 'Kelas' }}
                    @if($tahapanTitle) • <span class="text-indigo-600 font-black">{{ $tahapanTitle }}</span> @endif
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="hidden md:flex flex-col items-end">
                <span class="text-sm font-bold text-gray-800">{{ count($pesan) }} Pesan</span>
                <span class="text-[10px] text-green-500 font-black uppercase tracking-widest">Live Now</span>
            </div>
            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Chat Area -->
    <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 custom-scrollbar scroll-smooth" 
         id="chat-container" 
         x-init="
            $nextTick(() => { $el.scrollTop = $el.scrollHeight });
            window.addEventListener('scroll-chat-bottom', () => {
                setTimeout(() => { $el.scrollTop = $el.scrollHeight }, 50);
            });
         ">
        
        @if(empty($pesan))
            <div class="flex flex-col items-center justify-center h-full text-center py-12">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-200 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="message-square" class="w-10 h-10"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Mulai Diskusi Kelas</h3>
                <p class="text-gray-500 text-sm max-w-xs mx-auto">Tulis pesan pertama untuk memulai diskusi dengan seluruh isi kelas.</p>
            </div>
        @else
            @php $currentDate = null; @endphp
            @foreach($pesan as $p)
                @if($currentDate !== $p['tanggal'])
                    <div class="flex justify-center my-6">
                        <span class="px-3 py-1 bg-gray-200 text-gray-600 text-[10px] font-bold rounded-full uppercase tracking-wider shadow-sm border border-gray-300">
                            {{ $p['tanggal'] }}
                        </span>
                    </div>
                    @php $currentDate = $p['tanggal']; @endphp
                @endif

                <div class="flex {{ $p['is_me'] ? 'justify-end' : 'justify-start' }} group animate-in fade-in slide-in-from-bottom-2">
                    <div class="flex flex-col max-w-[85%] md:max-w-[70%] {{ $p['is_me'] ? 'items-end' : 'items-start' }}">
                        @if(!$p['is_me'])
                            <span class="text-[10px] font-black text-gray-400 ml-2 mb-1 uppercase tracking-widest">{{ $p['user_name'] }}</span>
                        @endif
                        
                        <div class="relative {{ $p['is_me'] ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-none shadow-indigo-200' : 'bg-white text-gray-800 border border-gray-100 rounded-2xl rounded-tl-none shadow-gray-200' }} p-3.5 shadow-lg relative transition-all hover:scale-[1.01]">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $p['pesan'] }}</p>
                            @if($p['lampiran'])
                                <div class="mt-2 pt-2 border-t {{ $p['is_me'] ? 'border-indigo-400' : 'border-gray-100' }}">
                                    <a href="{{ $p['lampiran'] }}" target="_blank" class="flex items-center gap-2 text-xs font-bold underline decoration-2">
                                        <i data-lucide="file" class="w-3.5 h-3.5"></i> Lihat Lampiran
                                    </a>
                                </div>
                            @endif
                            <div class="mt-1 flex items-center justify-end gap-1 opacity-60">
                                <span class="text-[9px] font-bold uppercase tracking-tighter">{{ $p['waktu'] }}</span>
                                @if($p['is_me'])
                                    <i data-lucide="check-check" class="w-3 h-3"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t border-gray-100 p-4 md:p-6 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] z-20">
        <form wire:submit.prevent="kirimPesan" class="max-w-4xl mx-auto">
            <div class="relative flex items-end gap-3">
                <div class="flex-1 relative">
                    <textarea 
                        wire:model="pesanBaru"
                        placeholder="Ketik sesuatu untuk seluruh kelas..."
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl pl-4 pr-12 py-3 text-sm focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition resize-none min-h-[50px] max-h-[150px] shadow-inner text-black"
                        rows="1"
                        x-data 
                        @keydown.enter.ctrl.prevent="$wire.kirimPesan()"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                    ></textarea>
                    
                    <button type="button" class="absolute right-3 bottom-3 text-gray-400 hover:text-indigo-600 p-1.5 rounded-lg transition">
                        <i data-lucide="paperclip" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <button type="submit" 
                        class="bg-indigo-600 text-white p-3.5 rounded-2xl hover:bg-indigo-700 transition active:scale-95 shadow-lg shadow-indigo-600/30 disabled:opacity-50 group"
                        wire:loading.attr="disabled"
                        wire:target="pesanBaru">
                    <i data-lucide="send" class="w-6 h-6 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" wire:loading.remove wire:target="kirimPesan"></i>
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin" wire:loading wire:target="kirimPesan"></i>
                </button>
            </div>
            <div class="mt-2 flex justify-between items-center px-2">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Press Ctrl + Enter to send</span>
                <div wire:loading wire:target="kirimPesan" class="text-[10px] text-indigo-600 font-black animate-pulse uppercase tracking-widest">Mengirim...</div>
            </div>
        </form>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</div>
