<div class="py-6">
    <x-slot name="title">Pusat Bantuan - Pro2Lms</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Pusat Bantuan</h1>
                <p class="text-gray-500 mt-1">Kelola tiket bantuan dan kendala teknis Anda</p>
            </div>
            <button wire:click="$set('showCreateForm', true)" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-black rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-95">
                <i data-lucide="plus-circle" class="mr-2 h-5 w-5"></i>
                Buat Tiket Baru
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Sidebar: Ticket List -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                        <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Tiket Saya</h2>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto custom-scrollbar">
                        @forelse($tickets as $ticket)
                            <button wire:click="selectTicket({{ $ticket->id }})" class="w-full text-left p-4 hover:bg-indigo-50/30 transition-colors relative {{ $selectedTicket && $selectedTicket->id == $ticket->id ? 'bg-indigo-50/50 border-l-4 border-indigo-600' : '' }}">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full 
                                        {{ $ticket->status == 'open' ? 'bg-amber-100 text-amber-700' : ($ticket->status == 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $ticket->status }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="font-bold text-gray-900 text-sm truncate">{{ $ticket->subjek }}</h3>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $ticket->kategori }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span class="text-[10px] font-bold {{ $ticket->prioritas == 'tinggi' ? 'text-rose-500' : ($ticket->prioritas == 'sedang' ? 'text-amber-500' : 'text-emerald-500') }} uppercase tracking-widest">{{ $ticket->prioritas }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="p-8 text-center">
                                <i data-lucide="inbox" class="mx-auto h-12 w-12 text-gray-200 mb-2"></i>
                                <p class="text-sm text-gray-400 font-medium">Belum ada tiket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Main Content: Ticket Detail or Form -->
            <div class="lg:col-span-8">
                @if($showCreateForm)
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-black text-gray-900">Buat Tiket Bantuan</h2>
                            <button wire:click="$set('showCreateForm', false)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <i data-lucide="x" class="h-6 w-6"></i>
                            </button>
                        </div>
                        <form wire:submit.prevent="createTicket" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Kategori</label>
                                    <select wire:model="kategori" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium h-12">
                                        <option value="bug">Bug / Kesalahan Sistem</option>
                                        <option value="akun">Masalah Akun / Login</option>
                                        <option value="kelas">Masalah Kelas / Materi</option>
                                        <option value="ujian">Masalah Ujian / Kuis</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Prioritas</label>
                                    <select wire:model="prioritas" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium h-12">
                                        <option value="rendah">Rendah (Saran/Tanya)</option>
                                        <option value="sedang">Sedang (Kendala Ringan)</option>
                                        <option value="tinggi">Tinggi (Mendesak/Eror)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Subjek / Judul</label>
                                <input type="text" wire:model="subjek" placeholder="Contoh: Tidak bisa kirim jawaban tugas" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium h-12">
                                @error('subjek') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">Detail Pesan</label>
                                <textarea wire:model="pesan" rows="6" placeholder="Jelaskan kendala Anda secara detail agar admin mudah membantu..." class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium"></textarea>
                                @error('pesan') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-[0.98]">
                                    Kirim Tiket Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif($selectedTicket)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full 
                                        {{ $selectedTicket->status == 'open' ? 'bg-amber-100 text-amber-700' : ($selectedTicket->status == 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $selectedTicket->status }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-bold">Tiket #{{ str_pad($selectedTicket->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <h2 class="text-xl font-black text-gray-900">{{ $selectedTicket->subjek }}</h2>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $selectedTicket->kategori }} · Diajukan {{ $selectedTicket->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            @if($selectedTicket->status != 'closed')
                                <button onclick="confirmCloseTicket({{ $selectedTicket->id }})" class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all text-xs font-black uppercase tracking-wider shadow-sm">
                                    Tutup Tiket
                                </button>
                            @endif
                        </div>
                        <div class="p-8 space-y-8">
                            <!-- User's Message -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                    <i data-lucide="user" class="h-5 w-5 text-indigo-600"></i>
                                </div>
                                <div class="flex-1 bg-gray-50 rounded-2xl p-5 text-gray-800 text-sm leading-relaxed border border-gray-100 shadow-sm italic">
                                    {!! nl2br(e($selectedTicket->pesan)) !!}
                                </div>
                            </div>

                            <!-- Admin's Response -->
                            @if($selectedTicket->balasan_admin)
                                <div class="flex gap-4">
                                    <div class="flex-1 bg-indigo-600 rounded-2xl p-5 text-white text-sm leading-relaxed shadow-lg relative ml-14">
                                        <div class="font-black text-[10px] uppercase tracking-widest mb-2 opacity-80 flex items-center gap-2">
                                            <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                                            Balasan Admin
                                        </div>
                                        {!! nl2br(e($selectedTicket->balasan_admin)) !!}
                                        <div class="text-[10px] mt-3 opacity-60 font-bold text-right italic">
                                            Dibalas {{ $selectedTicket->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center shrink-0 shadow-lg">
                                        <i data-lucide="user-cog" class="h-5 w-5 text-white font-bold"></i>
                                    </div>
                                </div>
                            @elseif($selectedTicket->status != 'closed')
                                <div class="flex gap-4 items-center justify-center py-12 border-2 border-dashed border-gray-100 rounded-2xl">
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                                            <i data-lucide="clock" class="h-6 w-6 text-amber-500"></i>
                                        </div>
                                        <p class="text-sm font-bold text-gray-400">Menunggu balasan admin...</p>
                                    </div>
                                </div>
                            @endif

                            @if($selectedTicket->status == 'closed')
                                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 flex items-center gap-4 text-emerald-800 animate-in zoom-in duration-300">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                                        <i data-lucide="badge-check" class="h-6 w-6 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-wider">Tiket Telah Selesai</p>
                                        <p class="text-xs font-bold opacity-80">Tiket ditutup pada {{ $selectedTicket->closed_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full min-h-[400px] flex flex-col items-center justify-center p-12 text-center">
                        <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
                            <i data-lucide="message-square" class="h-10 w-10 text-indigo-600"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 mb-2">Pilih Tiket Bantuan</h3>
                        <p class="text-gray-500 max-w-sm mb-8">Pilih tiket di sebelah kiri untuk melihat detail atau buat tiket baru jika Anda memiliki kendala teknis.</p>
                        <button wire:click="$set('showCreateForm', true)" class="text-indigo-600 font-black text-sm uppercase tracking-widest hover:text-indigo-700 transition flex items-center gap-2">
                             Mulai Sekarang <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function confirmCloseTicket(id) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Tutup Tiket?',
                    message: 'Pastikan masalah Anda sudah benar-benar terselesaikan sebelum menutup tiket ini.',
                    confirm: true,
                    confirmText: 'Ya, Tutup',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'closeTicket',
                        params: [id]
                    }
                }
            }));
        }
    </script>
</div>
