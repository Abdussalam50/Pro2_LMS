<div class="py-6">
    <x-slot name="title">Kelola FAQ - Admin</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Daftar FAQ</h1>
                <p class="text-gray-500 mt-1">Kelola pertanyaan yang sering diajukan oleh user</p>
            </div>
            <button wire:click="$set('showForm', true)" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-black rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-95">
                <i data-lucide="plus-circle" class="mr-2 h-5 w-5"></i>
                Tambah FAQ
            </button>
        </div>

        @if($showForm)
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mb-8 animate-in slide-in-from-top-4 duration-300">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-gray-900">{{ $editingId ? 'Edit FAQ' : 'Tambah FAQ Baru' }}</h2>
                    <button wire:click="$set('showForm', false)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
                <form wire:submit.prevent="{{ $editingId ? 'updateFaq' : 'createFaq' }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Pertanyaan</label>
                            <input type="text" wire:model="pertanyaan" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold h-12">
                            @error('pertanyaan') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Jawaban</label>
                            <textarea wire:model="jawaban" rows="4" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium"></textarea>
                            @error('jawaban') <span class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Kategori</label>
                            <input type="text" wire:model="kategori" list="faq_categories" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold h-12" placeholder="Contoh: Akun, Ujian, dll">
                            <datalist id="faq_categories">
                                <option value="Akun">
                                <option value="Ujian">
                                <option value="Materi">
                                <option value="Sistem">
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Urutan Tampil</label>
                            <input type="number" wire:model="urutan" class="block w-full rounded-xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold h-12">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-indigo-100 text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-[0.98]">
                            {{ $editingId ? 'Simpan Perubahan' : 'Terbitkan FAQ' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Urutan</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Pertanyaan</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($faqs as $faq)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 w-20">
                                <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-xs font-black">{{ $faq->urutan }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 text-sm mb-0.5">{{ $faq->pertanyaan }}</span>
                                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">{{ $faq->kategori ?? 'Tanpa Kategori' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleActive({{ $faq->id }})" class="p-1 px-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all {{ $faq->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $faq->is_active ? 'Aktif' : 'Draft' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <button wire:click="editFaq({{ $faq->id }})" class="p-2 text-amber-500 hover:bg-amber-50 rounded-xl transition-all">
                                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                                </button>
                                <button onclick="confirmDeleteFaq({{ $faq->id }})" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-bold whitespace-pre-line">Daftar FAQ masih kosong.
                             Tambahkan pertanyaan pertama Anda!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $faqs->links() }}
        </div>
    </div>

    <script>
        function confirmDeleteFaq(id) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus FAQ?',
                    message: 'Data ini akan segera dihapus permanen.',
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteFaq',
                        params: [id]
                    }
                }
            }));
        }
    </script>
</div>
