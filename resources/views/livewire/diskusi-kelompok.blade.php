@php $user = auth()->user(); @endphp

<div class="flex flex-col h-screen bg-gray-50" x-data="chatKelompok()" x-init="init()">
    {{-- Header --}}
    <div class="bg-white border-b px-6 py-4 flex items-center gap-3 shadow-sm">
        <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
            <span class="text-indigo-600 font-bold text-sm">{{ substr($kelompokData['nama_kelompok'] ?? 'K', 0, 2) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $kelompokData['nama_kelompok'] ?? 'Kelompok' }}</p>
            <p class="text-xs text-gray-400">Diskusi Kelompok @if($tahapanTitle) • <span class="text-indigo-600 font-bold">{{ $tahapanTitle }}</span> @endif</p>
        </div>
    </div>

    @if(!$kelompokId)
    <div class="flex-1 flex items-center justify-center text-gray-400 text-center p-8">
        <div class="max-w-md">
            <i data-lucide="users" class="w-16 h-16 mx-auto mb-4 opacity-20 text-indigo-600"></i>
            @if(Auth::user()->role === 'dosen')
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Pilih Kelompok Berdiskusi</h3>
                <p class="text-sm mt-2 text-gray-500">Sebagai dosen, Anda dapat memonitor diskusi kelompok tertentu dengan memilih tombol <span class="font-bold text-indigo-600">"Monitor"</span> pada Tab Diskusi di detail kelas.</p>
            @else
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Anda Belum Memiliki Kelompok</h3>
                <p class="text-sm mt-2 text-gray-500">Diskusi ini dikhususkan untuk anggota kelompok. Silakan hubungi dosen Anda untuk pengaturan kelompok.</p>
            @endif
        </div>
    </div>
    @else
    {{-- Chat Messages --}}
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3" id="chat-messages"
         @scroll-chat-bottom.window="scrollToBottom()">
        @foreach($pesan as $p)
        <div class="flex {{ $p['is_me'] ? 'justify-end' : 'justify-start' }}">
            @if(!$p['is_me'])
            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-2 flex-shrink-0">
                <span class="text-xs font-bold text-purple-600">{{ substr($p['user_name'], 0, 1) }}</span>
            </div>
            @endif
            <div class="max-w-xs lg:max-w-md">
                @if(!$p['is_me'])
                <p class="text-xs text-gray-400 mb-1 ml-1">{{ $p['user_name'] }}</p>
                @endif
                <div class="px-4 py-2 rounded-2xl {{ $p['is_me'] ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white text-gray-800 shadow-sm border border-gray-100 rounded-bl-sm' }}">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $p['pesan'] }}</p>
                    <p class="text-xs mt-1 {{ $p['is_me'] ? 'text-indigo-200' : 'text-gray-400' }} text-right">{{ $p['waktu'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Input --}}
    <div class="bg-white border-t px-4 py-3">
        <div class="flex items-end gap-3">
            <div class="flex-1 bg-gray-100 rounded-2xl px-4 py-2">
                <textarea wire:model="pesanBaru"
                    placeholder="Tulis pesan..."
                    rows="1"
                    class="w-full bg-transparent resize-none text-sm focus:outline-none max-h-24"
                    @keydown.enter.prevent="if(!$event.shiftKey) { $wire.kirimPesan().then(() => scrollToBottom()) }"></textarea>
            </div>
            <button wire:click="kirimPesan" wire:loading.attr="disabled"
                @click="$nextTick(() => scrollToBottom())"
                class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 transition flex-shrink-0 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>
    @endif
</div>

<script>
function chatKelompok() {
    return {
        init() {
            this.$nextTick(() => this.scrollToBottom());
            // Auto-refresh pesan setiap 5 detik (polling fallback)
            setInterval(() => { @this.loadPesan(); }, 5000);
        },
        scrollToBottom() {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
