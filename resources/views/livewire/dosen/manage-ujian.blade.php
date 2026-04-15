<div class="">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Ujian</h1>
            <p class="text-gray-600 text-sm">Buat dan kelola ujian untuk mahasiswa Anda.</p>
        </div>
        <button wire:click="openModal"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-200 flex items-center gap-2 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Tambah Ujian
        </button>
    </div>


    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <th class="p-4 font-semibold">Nama Ujian</th>
                        <th class="p-4 font-semibold">Mata Kuliah / Kelas</th>
                        <th class="p-4 font-semibold">Waktu</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ujians as $ujian)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="p-4">
                                            <div class="font-medium text-gray-800">{{ $ujian->nama_ujian }}</div>
                                            <div class="text-xs text-indigo-600 font-semibold">
                                                {{ $ujian->gradingComponent->name ?? strtoupper($ujian->jenis_ujian) }}</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-sm text-gray-700">{{ $ujian->mataKuliah->mata_kuliah }}</div>
                                            <div class="text-xs text-gray-500">{{ $ujian->kelas->kelas }} ({{ $ujian->kelas->kode }})
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-xs text-gray-600">Mulai: {{ $ujian->waktu_mulai->format('d M Y H:i') }}
                                            </div>
                                            <div class="text-xs text-gray-600">Selesai: {{ $ujian->waktu_selesai->format('d M Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <button wire:click="toggleActive('{{ $ujian->ujian_id }}')"
                                                    class="w-full px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $ujian->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-50 text-gray-400 border border-gray-100' }}">
                                                    {{ $ujian->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                </button>
                                                <button wire:click="toggleOpen('{{ $ujian->ujian_id }}')"
                                                    class="w-full px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $ujian->is_open ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-gray-50 text-gray-400 border border-gray-100' }}">
                                                    {{ $ujian->is_open ? 'Terbuka' : 'Tertutup' }}
                                                </button>
                                                <div
                                                    class="w-full px-3 py-1 flex items-center justify-center gap-1.5 rounded-full text-[9px] font-black uppercase tracking-widest cursor-default shadow-sm
                                                        {{ $ujian->mode_batasan === 'strict' ? 'bg-rose-50 text-rose-600 border border-rose-100' :
                        ($ujian->mode_batasan === 'materi_only' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-white text-gray-500 border border-gray-200') }}">
                                                    @if($ujian->mode_batasan === 'strict')
                                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i> Ketat
                                                    @elseif($ujian->mode_batasan === 'materi_only')
                                                        <i data-lucide="book-open" class="w-2.5 h-2.5"></i> Materi Saja
                                                    @else
                                                        <i data-lucide="globe" class="w-2.5 h-2.5"></i> Terbuka
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex justify-center gap-2">
                                                @if($ujian->gradingComponent && $ujian->gradingComponent->mapping_type === 'manual')
                                                    <a href="{{ route('dosen.ujians.manual', $ujian->ujian_id) }}"
                                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                                        title="Input Nilai Manual">
                                                        <i data-lucide="edit" class="w-5 h-5"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('dosen.ujians.soals', $ujian->ujian_id) }}"
                                                        class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                        title="Kelola Soal">
                                                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('dosen.ujians.hasil', $ujian->ujian_id) }}"
                                                    class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                    title="Lihat Hasil">
                                                    <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                                                </a>
                                                <button wire:click="openModal('{{ $ujian->ujian_id }}')"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Ujian">
                                                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                                                </button>
                                                <button onclick="confirmDelete('{{ $ujian->ujian_id }}', '{{ $ujian->nama_ujian }}')"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500 italic">Belum ada data ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(id, name) {
                window.dispatchEvent(new CustomEvent('swal', {
                    detail: {
                        icon: 'warning',
                        title: 'Hapus Ujian?',
                        message: `Apakah Anda yakin ingin menghapus ujian "${name}"? Semua data nilai mahasiswa terkait juga akan terhapus.`,
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
        </script>
    @endpush

    {{-- ===== UJIAN MODAL ===== --}}
    @if($showUjianModal)
        @teleport('body')
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="background: rgba(17,24,39,0.6); backdrop-filter: blur(4px);">
            <div
                class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-gray-100 overflow-y-auto max-h-[90vh]">
                <div class="p-8 md:p-10">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 tracking-tight">
                                    {{ $ujianForm['ujian_id'] ? 'Edit Ujian' : 'Tambah Ujian Baru' }}</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Konfigurasi
                                    parameter ujian mahasiswa</p>
                            </div>
                        </div>
                        <button wire:click="$set('showUjianModal', false)"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveUjian" class="space-y-6">
                        {{-- Mata Kuliah & Kelas --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Mata
                                    Kuliah</label>
                                <select wire:model.live="ujianForm.mata_kuliah_id"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">-- Pilih Matkul --</option>
                                    @foreach($mataKuliahs as $mk)
                                        <option value="{{ $mk->mata_kuliah_id }}">{{ $mk->mata_kuliah }}</option>
                                    @endforeach
                                </select>
                                @error('ujianForm.mata_kuliah_id') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kelas</label>
                                <select wire:model.live="ujianForm.kelas_id"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelases->where('mata_kuliah_id', $ujianForm['mata_kuliah_id']) as $kelas)
                                        <option value="{{ $kelas->kelas_id }}">{{ $kelas->kelas }}</option>
                                    @endforeach
                                </select>
                                @error('ujianForm.kelas_id') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Nama Ujian --}}
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama
                                Ujian</label>
                            <input type="text" wire:model="ujianForm.nama_ujian"
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                placeholder="Contoh: UTS Aljabar Linear">
                            @error('ujianForm.nama_ujian') <span
                            class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi
                                & Instruksi</label>
                            <textarea wire:model="ujianForm.deskripsi"
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-24"
                                placeholder="Tulis instruksi pengerjaan..."></textarea>
                        </div>

                        {{-- Jenis, Jumlah Soal, Bobot --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div class="relative">
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Komponen
                                    Penilaian</label>
                                <select wire:model.live="ujianForm.grading_component_id"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="">-- Pilih Komponen --</option>
                                    @foreach($gradingComponents as $comp)
                                        @if(in_array($comp->mapping_type, ['exam', 'manual']))
                                            <option value="{{ $comp->id }}">
                                                {{ $comp->name }} ({{ $comp->mapping_type == 'manual' ? 'Manual' : 'Online' }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('ujianForm.grading_component_id') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jumlah
                                    Soal</label>
                                <input type="number" wire:model="ujianForm.jumlah_soal"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                                @error('ujianForm.jumlah_soal') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot
                                    (%)</label>
                                <input type="number" wire:model="ujianForm.bobot_nilai"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>

                        {{-- Waktu --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu
                                    Mulai</label>
                                <input type="datetime-local" wire:model="ujianForm.waktu_mulai"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                @error('ujianForm.waktu_mulai') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Waktu
                                    Selesai</label>
                                <input type="datetime-local" wire:model="ujianForm.waktu_selesai"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                @error('ujianForm.waktu_selesai') <span
                                class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Toggle: Aktif, Buka Akses, Acak Soal --}}
                        <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-3xl border border-gray-100">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model="ujianForm.is_active"
                                    class="h-5 w-5 rounded-lg border-gray-200 text-indigo-600 cursor-pointer">
                                <span
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Aktif</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model="ujianForm.is_open"
                                    class="h-5 w-5 rounded-lg border-gray-200 text-indigo-600 cursor-pointer">
                                <span
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Buka
                                    Akses</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model="ujianForm.is_random"
                                    class="h-5 w-5 rounded-lg border-gray-200 text-indigo-600 cursor-pointer">
                                <span
                                    class="text-[10px] font-black text-indigo-600 uppercase tracking-widest group-hover:text-indigo-700 transition-colors">Acak
                                    Soal</span>
                            </label>
                        </div>

                        {{-- Mode Batasan --}}
                        <div>
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Mode
                                Batasan Ujian</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label
                                    class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $ujianForm['mode_batasan'] === 'open' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="ujianForm.mode_batasan" value="open"
                                        class="sr-only">
                                    <i data-lucide="globe"
                                        class="w-5 h-5 mb-2 {{ $ujianForm['mode_batasan'] === 'open' ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'open' ? 'text-indigo-700' : 'text-gray-600' }}">Terbuka</span>
                                    <p class="text-[9px] text-gray-400 mt-1">Bebas hambatan</p>
                                </label>

                                <label
                                    class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 bg-white hover:bg-amber-50' }}">
                                    <input type="radio" wire:model.live="ujianForm.mode_batasan" value="materi_only"
                                        class="sr-only">
                                    <i data-lucide="book-open"
                                        class="w-5 h-5 mb-2 {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'text-amber-600' : 'text-gray-400' }}"></i>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'materi_only' ? 'text-amber-700' : 'text-gray-600' }}">Buka
                                        Materi</span>
                                    <p class="text-[9px] text-gray-400 mt-1">Izinkan akses materi PDF</p>
                                </label>

                                <label
                                    class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $ujianForm['mode_batasan'] === 'strict' ? 'border-rose-500 bg-rose-50' : 'border-gray-200 bg-white hover:bg-rose-50' }}">
                                    <input type="radio" wire:model.live="ujianForm.mode_batasan" value="strict"
                                        class="sr-only">
                                    <i data-lucide="lock"
                                        class="w-5 h-5 mb-2 {{ $ujianForm['mode_batasan'] === 'strict' ? 'text-rose-600' : 'text-gray-400' }}"></i>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'strict' ? 'text-rose-700' : 'text-gray-600' }}">Ketat
                                        (Lock)</span>
                                    <p class="text-[9px] text-gray-400 mt-1">Layar penuh dikunci</p>
                                </label>

                                <label
                                    class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $ujianForm['mode_batasan'] === 'custom' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-purple-50' }}">
                                    <input type="radio" wire:model.live="ujianForm.mode_batasan" value="custom"
                                        class="sr-only">
                                    <i data-lucide="code"
                                        class="w-5 h-5 mb-2 {{ $ujianForm['mode_batasan'] === 'custom' ? 'text-purple-600' : 'text-gray-400' }}"></i>
                                    <span
                                        class="text-xs font-black uppercase tracking-widest {{ $ujianForm['mode_batasan'] === 'custom' ? 'text-purple-700' : 'text-gray-600' }}">Custom
                                        Logic</span>
                                    <p class="text-[9px] text-gray-400 mt-1">Gunakan handler khusus</p>
                                </label>
                            </div>

                            @if($ujianForm['mode_batasan'] === 'custom')
                                <div
                                    class="mt-4 p-4 bg-purple-50 rounded-2xl border border-purple-100 animate-in fade-in slide-in-from-top-2">
                                    <label
                                        class="block text-[10px] font-black text-purple-400 uppercase tracking-widest mb-2 pl-1">Handler
                                        Class (FQCN)</label>
                                    <input type="text" wire:model="ujianForm.custom_handler"
                                        class="w-full bg-white border border-purple-200 rounded-xl p-3 text-xs font-medium focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"
                                        placeholder="App\Services\Ujian\CustomUjianHandler">
                                    <p class="text-[9px] text-purple-400 mt-2 italic font-medium">* Pastikan class
                                        mengimplementasikan UjianHandlerInterface</p>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showUjianModal', false)"
                                class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                            <button type="submit"
                                class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove
                                    wire:target="saveUjian">{{ $ujianForm['ujian_id'] ? 'Update Ujian' : 'Simpan Ujian' }}</span>
                                <span wire:loading wire:target="saveUjian">Menyimpan...</span>
                                <i data-lucide="save" class="w-3.5 h-3.5" wire:loading.remove wire:target="saveUjian"></i>
                            </button>
                        </div>
                    </form>
                </div>{{-- /padding wrapper --}}
            </div>
        </div>
        @endteleport
    @endif

</div>