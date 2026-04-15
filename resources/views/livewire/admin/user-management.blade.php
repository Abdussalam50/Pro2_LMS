<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/40 shadow-xl shadow-indigo-900/5">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen User</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Kelola Admin, Dosen, dan Mahasiswa</p>
        </div>
        <button wire:click="openModal" class="flex items-center gap-3 px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 group">
            <i data-lucide="user-plus" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
            Tambah User Baru
        </button>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white/60 backdrop-blur-md p-6 rounded-3xl border border-white/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-gray-900 leading-none">{{ \App\Models\User::count() }}</div>
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Total User</div>
            </div>
        </div>
        <!-- Add more stats if needed -->
    </div>

    <!-- Search & Tabs -->
    <div class="space-y-6">
        <div class="relative group">
            <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
            </div>
            <input wire:model.live="search" type="text" placeholder="Cari berdasarkan nama atau email..." class="w-full bg-white/80 backdrop-blur-md border-none rounded-[2rem] py-5 pl-16 pr-8 shadow-sm focus:ring-4 focus:ring-indigo-500/10 font-medium transition-all" />
        </div>

        <div class="flex items-center gap-2 p-1.5 bg-gray-100/50 rounded-2xl w-fit backdrop-blur-sm border border-gray-200/50">
            <button wire:click="setTab('mahasiswa')" class="px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentTab === 'mahasiswa' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                Mahasiswa
            </button>
            <button wire:click="setTab('dosen')" class="px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentTab === 'dosen' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                Dosen
            </button>
            <button wire:click="setTab('admin')" class="px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentTab === 'admin' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                Administrator
            </button>
            <button wire:click="setTab('pending')" class="px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $currentTab === 'pending' ? 'bg-white text-rose-600 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                Menunggu ACC <span class="ml-1 bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full">{{ \App\Models\User::where('is_active', false)->where('role', '!=', 'admin')->count() }}</span>
            </button>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white/40 backdrop-blur-xl rounded-[3rem] border border-white/40 shadow-2xl shadow-indigo-900/5 overflow-hidden ring-1 ring-black/[0.01]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">User Profile</th>
                        @if($currentTab === 'mahasiswa')
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">NIM / Kelas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">Prodi / Angkatan</th>
                        @elseif($currentTab === 'dosen')
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">Kode Dosen</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">Kontak</th>
                        @elseif($currentTab === 'pending')
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">Role / Prodi</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50">Tgl Daftar</th>
                        @endif
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50 text-center">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100/50 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/50">
                    @forelse($users as $user)
                        <tr class="group hover:bg-white/80 transition-all duration-300">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100/30 flex items-center justify-center font-black text-indigo-500 shadow-inner group-hover:scale-110 transition-transform">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 tracking-tight group-hover:text-indigo-600 transition-colors">{{ $user->name }}</div>
                                        <div class="text-[11px] font-bold text-gray-400 mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            @if($currentTab === 'mahasiswa')
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-black text-gray-700">{{ $user->mahasiswa->nim ?? '-' }}</div>
                                    <div class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">{{ $user->mahasiswa->kelas->kelas ?? 'Tanpa Kelas' }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-bold text-gray-600">{{ $user->mahasiswa->program_studi ?? '-' }}</div>
                                    <div class="text-[10px] font-medium text-gray-400">Angkatan {{ $user->mahasiswa->angkatan ?? '-' }}</div>
                                </td>
                            @elseif($currentTab === 'dosen')
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-black text-gray-700">{{ $user->dosen->kode ?? '-' }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-bold text-gray-600">{{ $user->dosen->no_wa ?? '-' }}</div>
                                </td>
                            @elseif($currentTab === 'pending')
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-black text-gray-700 capitalize">{{ $user->role }}</div>
                                    <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">{{ $user->mahasiswa->program_studi ?? ($user->dosen->kode ?? '-') }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[11px] font-bold text-gray-600">{{ $user->created_at->format('d M Y') }}</div>
                                    <div class="text-[9px] font-medium text-gray-400">{{ $user->created_at->format('H:i') }} WIB</div>
                                </td>
                            @endif

                            <td class="px-8 py-6 text-center">
                                @if($currentTab === 'pending')
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu ACC
                                    </span>
                                @else
                                    <button wire:click="toggleStatus('{{ $user->id }}')" 
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all {{ $user->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                @endif
                            </td>

                            <td class="px-8 py-6 text-right space-x-2">
                                @if($currentTab === 'pending')
                                    <button wire:click="approveUser('{{ $user->id }}')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" title="Setujui"><i data-lucide="check-circle" class="w-5 h-5"></i></button>
                                    <button onclick="confirmRejectUser('{{ $user->id }}', '{{ $user->name }}')" class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Tolak Pendaftaran"><i data-lucide="x-circle" class="w-5 h-5"></i></button>
                                @else
                                    <button wire:click="openModal('{{ $user->id }}')" class="p-3 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"><i data-lucide="edit-3" class="w-5 h-5"></i></button>
                                    @if($user->id !== auth()->id())
                                        <button onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->name }}')" class="p-3 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center opacity-30 grayscale">
                                <i data-lucide="user-minus" class="w-16 h-16 mx-auto mb-4 font-thin"></i>
                                <p class="font-black uppercase tracking-widest text-xs">User Tidak Ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-8 border-t border-gray-100/50 bg-gray-50/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function confirmDeleteUser(id, name) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus User?',
                    message: `Apakah Anda yakin ingin menghapus user "${name}"? Tindakan ini tidak dapat dibatalkan.`,
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'delete',
                        params: [id]
                    }
                }
            }));
        }

        function confirmRejectUser(id, name) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Tolak Pendaftaran?',
                    message: `Apakah Anda yakin ingin menolak & menghapus pendaftaran user "${name}"?`,
                    confirm: true,
                    confirmText: 'Ya, Tolak',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'rejectUser',
                        params: [id]
                    }
                }
            }));
        }
    </script>
    @endpush

    <!-- Modal User -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white/90 backdrop-blur-2xl p-10 rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-2xl border border-white/40 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50 shadow-sm">
                            <i data-lucide="{{ $editingUser ? 'user-cog' : 'user-plus' }}" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ $editingUser ? 'Edit User' : 'Tambah User' }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Lengkapi informasi akses berikut</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-3 text-gray-400 hover:text-gray-900 transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
                </div>

                <form wire:submit.prevent="save" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nama Lengkap</label>
                            <input wire:model="name" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all" placeholder="Contoh: Budi Santoso" />
                            @error('name') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Email Institusi</label>
                            <input wire:model="email" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all" placeholder="email@example.com" />
                            @error('email') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Password {{ $editingUser ? '(Kosongi jika tidak diubah)' : '' }}</label>
                            <input wire:model="password" type="password" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all" />
                            @error('password') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Hak Akses Role</label>
                            <select wire:model.live="role" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all appearance-none">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 transition-all"></div>
                                <span class="ml-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Akun Aktif</span>
                            </label>
                        </div>
                    </div>

                    @if($role === 'mahasiswa')
                        <div class="pt-8 border-t border-gray-100 animate-in slide-in-from-top-4 duration-500">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] mb-6">Informasi Mahasiswa</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nomor Induk Mahasiswa (NIM)</label>
                                    <input wire:model="nim" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                    @error('nim') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Pilih Kelas</label>
                                    <select wire:model="kelas_id" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->kelas_id }}">{{ $kelas->kelas }}</option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Program Studi</label>
                                    <input wire:model="program_studi" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Angkatan</label>
                                    <input wire:model="angkatan" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="2024" />
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($role === 'dosen')
                        <div class="pt-8 border-t border-gray-100 animate-in slide-in-from-top-4 duration-500">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] mb-6">Informasi Dosen</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Kode Dosen (NIDN/Inisial)</label>
                                    <input wire:model="kode_dosen" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                    @error('kode_dosen') <span class="text-[10px] font-bold text-rose-500 mt-2 block pl-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nomor WhatsApp</label>
                                    <input wire:model="no_wa" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-4 pt-8 border-t border-gray-100/50">
                        <button type="button" wire:click="closeModal" class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Batal</button>
                        <button type="submit" class="px-12 py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
