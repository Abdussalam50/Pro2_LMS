<div>
    @if($unreadCount > 0)
        <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-black leading-none text-white bg-red-500 rounded-full shadow-sm animate-pulse">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</div>
