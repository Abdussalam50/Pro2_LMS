<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false" x-on:fcm-message.window="$wire.refreshNotifications()">
    <button @click="open = !open" class="relative p-2 text-indigo-300 hover:text-white transition-colors duration-200 focus:outline-none group">
        <i data-lucide="bell" class="w-5 h-5"></i>
        @if($unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] items-center justify-center font-bold text-white shadow-sm border border-white/20">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute top-full right-0 mt-4 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-x-scroll w-[200px]"
        x-cloak
    >
        <div class="p-4 border-b border-gray-50 bg-indigo-50/30 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-800">Notifikasi</h3>
                @if($unreadCount > 0)
                    <span class="text-[10px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-bold uppercase">{{ $unreadCount }} Baru</span>
                @endif
            </div>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead" 
                    class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition uppercase tracking-tight"
                >
                    Tandai semua dibaca
                </button>
            @endif
        </div>

        <div class="max-h-[350px] overflow-y-auto custom-scrollbar">
            @forelse($notifications as $notif)
                <div 
                    wire:key="notif-{{ $notif->notifikasi_id }}"
                    class="p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors group relative"
                >
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600 shadow-sm border border-indigo-200">
                                <i data-lucide="{{ $notif->tipe === 'pengumuman' ? 'megaphone' : 'bell' }}" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0 pr-6">
                            <p class="text-xs font-bold text-gray-800 truncate mb-0.5 text-balance">
                                {{ $notif->data['title'] ?? 'Notifikasi Baru' }}
                            </p>
                            <p class="text-[11px] text-gray-600 line-clamp-2 leading-relaxed">
                                {{ $notif->data['body'] ?? '' }}
                            </p>
                            <p class="text-[9px] text-gray-400 mt-1.5 flex items-center gap-1">
                                <i data-lucide="clock" class="w-2.5 h-2.5"></i>
                                {{ $notif->created_at->diffForHumans() }}
                            </p>
                        </div>
                        
                        <!-- Mark as Read Button (Individual) -->
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button 
                                wire:click="markAsRead('{{ $notif->notifikasi_id }}')"
                                class="p-1.5 rounded-lg bg-white border border-gray-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition shadow-sm"
                                title="Tandai dibaca"
                            >
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <div class="flex-shrink-0 self-center group-hover:opacity-0 transition-opacity">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 shadow-sm shadow-indigo-200"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-inner">
                        <i data-lucide="party-popper" class="text-indigo-300 w-8 h-8"></i>
                    </div>
                    <p class="text-[13px] font-bold text-gray-700">Yippie!</p>
                    <p class="text-xs text-gray-400 mt-1">Semua notifikasi sudah dibaca.</p>
                </div>
            @endforelse
        </div>

        <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
            <a href="#" class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition">Lihat Riwayat</a>
        </div>
    </div>
</div>
