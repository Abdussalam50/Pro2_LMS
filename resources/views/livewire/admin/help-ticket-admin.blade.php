<div class="py-6">
    <x-slot name="title">Manajemen Tiket Bantuan - Admin</x-slot>

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Tiket</h1>
            <p class="text-gray-500 mt-1">Review dan tangani kendala teknis dari user</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Ticket Table / List -->
            <div class="xl:col-span-8 space-y-6">
                <!-- Filters and Search -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari subjek, pesan, atau nama user..." class="block w-full pl-10 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <select wire:model.live="statusFilter" class="bg-gray-50 border-none rounded-xl text-xs font-black uppercase tracking-widest px-4 focus:ring-2 focus:ring-indigo-500 py-3">
                            <option value="">Semua Status</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </select>
                        <select wire:model.live="kategoriFilter" class="bg-gray-50 border-none rounded-xl text-xs font-black uppercase tracking-widest px-4 focus:ring-2 focus:ring-indigo-500 py-3">
                            <option value="">Semua Kategori</option>
                            <option value="bug">Bug</option>
                            <option value="akun">Akun</option>
                            <option value="kelas">Kelas</option>
                            <option value="ujian">Ujian</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">User & Subjek</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Kategori</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Prioritas</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ $selectedTicket && $selectedTicket->id == $ticket->id ? 'bg-indigo-50/30 ring-1 ring-inset ring-indigo-500/20' : '' }}">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm
                                            {{ $ticket->status == 'open' ? 'bg-amber-100 text-amber-700' : ($ticket->status == 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                {{ substr($ticket->user->name, 0, 2) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-gray-900 text-sm truncate">{{ $ticket->subjek }}</p>
                                                <p class="text-[10px] text-gray-400 font-medium">{{ $ticket->user->name }} · {{ $ticket->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">{{ $ticket->kategori }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[10px] font-black uppercase tracking-widest
                                             {{ $ticket->prioritas == 'tinggi' ? 'text-rose-500' : ($ticket->prioritas == 'sedang' ? 'text-amber-500' : 'text-emerald-500') }}">
                                            {{ $ticket->prioritas }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button wire:click="selectTicket({{ $ticket->id }})" class="p-2 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 rounded-xl transition-all">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                        </button>
                                        <button onclick="confirmDeleteTicket({{ $ticket->id }})" class="p-2 text-rose-500 hover:bg-rose-50 hover:text-rose-600 rounded-xl transition-all">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-bold">Tidak ada tiket ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            </div>

            <!-- Ticket Detail / Reply Panel -->
            <div class="xl:col-span-4">
                @if($selectedTicket)
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-8 animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h2 class="text-xl font-black text-gray-900 tracking-tight">Detail Tiket</h2>
                            <button wire:click="$set('selectedTicket', null)" class="p-1 px-3 text-xs bg-gray-100 text-gray-400 rounded-lg font-black uppercase tracking-widest hover:bg-gray-200">Close</button>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Info Section -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Pengirim</p>
                                    <p class="text-xs font-bold text-gray-800">{{ $selectedTicket->user->name }}</p>
                                    <p class="text-[9px] font-bold text-indigo-500">{{ $selectedTicket->user->email }}</p>
                                </div>
                                <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-50">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Waktu</p>
                                    <p class="text-xs font-bold text-gray-800">{{ $selectedTicket->created_at->format('d/m/Y H:i') }}</p>
                                    <p class="text-[9px] font-bold text-amber-500">{{ $selectedTicket->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <!-- Pesan User -->
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Pesan User</p>
                                <div class="bg-indigo-50/50 p-5 rounded-2xl text-sm text-gray-800 leading-relaxed border border-indigo-100/50 italic">
                                    {!! nl2br(e($selectedTicket->pesan)) !!}
                                </div>
                            </div>

                            <hr class="border-gray-50">

                            <!-- Admin Action -->
                            <form wire:submit.prevent="updateTicket" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Ubah Status</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach(['open', 'in_progress', 'closed'] as $st)
                                            <button type="button" wire:click="$set('newStatus', '{{ $st }}')" 
                                                class="py-2.5 px-3 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all shadow-sm
                                                {{ $newStatus == $st ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-400 border-gray-100 hover:border-gray-300' }}">
                                                {{ $st }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Balasan Admin</label>
                                    <textarea wire:model="balasan_admin" rows="6" class="block w-full rounded-2xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium" placeholder="Tulis jawaban atau solusi di sini..."></textarea>
                                    @error('balasan_admin') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-indigo-100 text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-[0.98]">
                                    Update & Kirim Balasan
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-indigo-900 rounded-3xl p-8 text-white text-center shadow-2xl relative overflow-hidden h-[500px] flex flex-col justify-center border-4 border-indigo-800">
                         <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                         <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                         
                         <div class="relative z-10">
                            <i data-lucide="shield-question" class="h-16 w-16 mx-auto mb-6 opacity-30"></i>
                            <h3 class="text-xl font-black mb-2 tracking-tight">Pilih Tiket Untuk Direview</h3>
                            <p class="text-indigo-200 text-sm font-medium leading-relaxed max-w-[250px] mx-auto opacity-80">Klik tombol mata pada tabel untuk melihat detail dan memberikan balasan solusi kepada user.</p>
                         </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteTicket(id) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Tiket?',
                    message: 'Tindakan ini permanen dan tidak bisa dibatalkan.',
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteTicket',
                        params: [id]
                    }
                }
            }));
        }
    </script>
</div>
