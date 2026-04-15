<div class="py-6">
    <x-slot name="title">Kelola Periode & Bobot Akademik - Admin</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Periode & Semester</h1>
                <p class="text-gray-500 mt-1">Kelola semester berjalan dan atur bobot penilaian akhir</p>
            </div>
            <button wire:click="$set('showForm', true)" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-black rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-95">
                <i data-lucide="plus-circle" class="mr-2 h-5 w-5"></i>
                Tambah Periode Baru
            </button>
        </div>

        @if($showForm)
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mb-8 animate-in slide-in-from-top-4 duration-300">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-gray-900">{{ $editingId ? 'Edit Periode' : 'Tambah Periode Akademik' }}</h2>
                    <button wire:click="$set('showForm', false)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
                <form wire:submit.prevent="{{ $editingId ? 'updatePeriod' : 'createPeriod' }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Nama Periode</label>
                            <input type="text" wire:model="name" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 font-bold h-12 px-4" placeholder="Contoh: 2024/2025 Ganjil">
                            @error('name') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Tahun Akademik</label>
                            <input type="text" wire:model="tahun" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 font-bold h-12 px-4" placeholder="Contoh: 2024/2025">
                            @error('tahun') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Semester</label>
                            <select wire:model="semester" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 font-bold h-12 px-4">
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                            @error('semester') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Weights Group -->
                        <div class="md:col-span-2 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <i data-lucide="percent" class="h-4 w-4"></i> Komposisi Bobot Nilai Akhir
                            </h3>
                            <div class="grid grid-cols-3 gap-6 bg-indigo-50/30 p-6 rounded-2xl border border-indigo-100/50">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Bobot Tugas (%)</label>
                                    <input type="number" wire:model="weight_task" class="block w-full rounded-xl border-white bg-white shadow-sm focus:ring-0 font-black h-12 px-4 text-center text-indigo-600 text-xl">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Bobot UTS (%)</label>
                                    <input type="number" wire:model="weight_mid" class="block w-full rounded-xl border-white bg-white shadow-sm focus:ring-0 font-black h-12 px-4 text-center text-indigo-600 text-xl">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Bobot UAS (%)</label>
                                    <input type="number" wire:model="weight_final" class="block w-full rounded-xl border-white bg-white shadow-sm focus:ring-0 font-black h-12 px-4 text-center text-indigo-600 text-xl">
                                </div>
                                <div class="col-span-3 text-center">
                                    <p class="text-xs font-bold {{ ($weight_task + $weight_mid + $weight_final) === 100 ? 'text-emerald-500' : 'text-rose-500' }}">
                                        Total Bobot: {{ $weight_task + $weight_mid + $weight_final }}% 
                                        @if(($weight_task + $weight_mid + $weight_final) !== 100)
                                            <span class="ml-2 italic">(Harus 100%)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <button type="submit" class="flex-1 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-indigo-100 text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-[0.98]">
                            {{ $editingId ? 'Update Periode' : 'Buat Periode' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Periode</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Bobot (T:U:A)</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 whitespace-nowrap">
                    @forelse($periods as $period)
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $period->is_active ? 'bg-indigo-50/20' : '' }}">
                            <td class="px-6 py-4">
                                @if($period->is_active)
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <button wire:click="setActive({{ $period->id }})" class="bg-gray-100 text-gray-400 hover:bg-indigo-100 hover:text-indigo-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest transition-all">
                                        Aktifkan
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 text-sm mb-0.5">{{ $period->name }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $period->tahun }} · {{ ucfirst($period->semester) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="bg-indigo-50 text-indigo-600 font-bold px-2 py-1 rounded text-xs">{{ $period->weight_task }}%</span>
                                    <span class="text-gray-300">:</span>
                                    <span class="bg-amber-50 text-amber-600 font-bold px-2 py-1 rounded text-xs">{{ $period->weight_mid }}%</span>
                                    <span class="text-gray-300">:</span>
                                    <span class="bg-rose-50 text-rose-600 font-bold px-2 py-1 rounded text-xs">{{ $period->weight_final }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="editPeriod({{ $period->id }})" class="p-2 text-amber-500 hover:bg-amber-50 rounded-xl transition-all">
                                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                                    </button>
                                    @if(!$period->is_active)
                                        <button onclick="confirmDeletePeriod({{ $period->id }})" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-bold">Belum ada periode akademik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $periods->links() }}
        </div>
    </div>

    <script>
        function confirmDeletePeriod(id) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Periode?',
                    message: 'Pastikan periode ini tidak digunakan oleh kelas manapun.',
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deletePeriod',
                        params: [id]
                    }
                }
            }));
        }
    </script>
</div>
