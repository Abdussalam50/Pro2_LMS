<div class="min-h-screen bg-[#f8fafc] p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 md:mb-12">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Bank Soal & Tugas</h1>
                <p class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em] mt-1">Pusat kendali koleksi materi evaluasi mandiri Anda</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari soal..." class="pl-11 pr-4 py-2.5 bg-white border border-gray-100 rounded-2xl text-sm w-full md:w-64 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none shadow-sm shadow-gray-200/50">
                </div>

                <select wire:model.live="filterJenis" class="bg-white border border-gray-100 rounded-2xl px-4 py-2.5 text-sm font-bold text-gray-600 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none shadow-sm shadow-gray-200/50">
                    <option value="">Semua Tipe</option>
                    <option value="ujian">Ujian</option>
                    <option value="tugas">Tugas</option>
                </select>
            </div>
        </div>


        {{-- Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($bankSoals as $soal)
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/5 hover:-translate-y-1 transition-all duration-300 flex flex-col group overflow-hidden">
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $soal->jenis === 'ujian' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                                    {{ $soal->jenis }}
                                </span>
                                <span class="px-3 py-1 rounded-full bg-gray-50 border border-gray-100 text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    {{ str_replace('_', ' ', $soal->tipe_soal) }}
                                </span>
                            </div>
                            <div class="text-[10px] text-gray-300 font-bold uppercase tracking-widest">
                                {{ $soal->created_at->format('d M y') }}
                            </div>
                        </div>

                        <h3 class="font-bold text-gray-800 text-sm line-clamp-2 leading-relaxed mb-4 min-h-[2.8rem]">
                            {{ $soal->judul_soal }}
                        </h3>

                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                            <button wire:click="showPreview('{{ $soal->bank_soal_id }}')" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Preview
                            </button>
                            
                            <button onclick="confirmDeleteBankSoal('{{ $soal->bank_soal_id }}', '{{ addslashes($soal->judul_soal) }}')" class="w-8 h-8 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:bg-rose-50 hover:text-rose-600 transition-all opacity-0 group-hover:opacity-100 focus:opacity-100">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-300 mb-4">
                        <i data-lucide="inbox" class="w-10 h-10"></i>
                    </div>
                    <p class="font-black text-gray-400 uppercase tracking-widest text-sm text-center">Belum ada koleksi soal ditemukan</p>
                    <p class="text-xs text-gray-300 mt-2">Soal otomatis tersimpan saat Anda membuat Ujian atau Tugas Baru.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $bankSoals->links() }}
        </div>
    </div>

    {{-- Preview Modal --}}
    @if($previewSoal)
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md z-[9999] flex items-center justify-center p-4" x-data x-init="if(typeof renderMath === 'function') renderMath();">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                            <i data-lucide="database" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Detail Soal Bank</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Preview konten evaluasi</p>
                        </div>
                    </div>
                    <button wire:click="closePreview" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto flex-1">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2 block pl-1">Konten Soal</span>
                            <div class="prose max-w-none text-gray-800 font-medium leading-relaxed bg-gray-50 rounded-2xl p-6 border border-gray-100 math-container">
                                {!! $previewSoal->konten_soal !!}
                            </div>
                        </div>

                        {{-- Untuk Tugas Komprehensif (JSON) --}}
                        @if($previewSoal->tipe_soal === 'kompleks')
                            <div>
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2 block pl-1">Struktur Tugas</span>
                                <div class="bg-indigo-50/30 rounded-2xl p-6 border border-indigo-100">
                                     <h4 class="font-bold text-indigo-900 mb-4">{{ $previewSoal->opsi_jawaban['title'] ?? 'Tanpa Judul' }}</h4>
                                     <div class="space-y-4">
                                         @foreach($previewSoal->opsi_jawaban['main_soals'] ?? [] as $main)
                                            <div class="pl-4 border-l-2 border-indigo-200">
                                                <div class="text-sm font-medium text-indigo-800 mb-2">{!! $main['narasi'] !!}</div>
                                                @foreach($main['soals'] ?? [] as $s)
                                                    <div class="bg-white p-3 rounded-xl border border-indigo-50 text-xs text-indigo-700 mt-2">
                                                        {!! $s['pertanyaan'] !!}
                                                    </div>
                                                @endforeach
                                            </div>
                                         @endforeach
                                     </div>
                                </div>
                            </div>
                        @endif

                        {{-- Untuk Pilihan Ganda --}}
                        @if($previewSoal->tipe_soal === 'pilihan_ganda' && is_array($previewSoal->opsi_jawaban))
                            <div>
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2 block pl-1">Opsi Jawaban</span>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($previewSoal->opsi_jawaban as $key => $val)
                                        @if($val)
                                        <div class="flex items-center gap-4 p-4 rounded-xl border {{ $previewSoal->kunci_jawaban == $key ? 'bg-emerald-50 border-emerald-100' : 'bg-gray-50/50 border-gray-100' }}">
                                            <div class="w-8 h-8 rounded-lg {{ $previewSoal->kunci_jawaban == $key ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400' }} flex items-center justify-center font-black text-[11px] uppercase shadow-sm">
                                                {{ $key }}
                                            </div>
                                            <div class="text-sm {{ $previewSoal->kunci_jawaban == $key ? 'text-emerald-700' : 'text-gray-600' }} font-bold">
                                                {{ $val }}
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($previewSoal->jenis === 'ujian' && $previewSoal->tipe_soal === 'esai')
                            <div>
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2 block pl-1">Kunci Jawaban</span>
                                <div class="bg-amber-50 rounded-2xl p-6 border border-amber-100 text-sm text-amber-800 font-medium italic">
                                    {!! $previewSoal->kunci_jawaban !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button wire:click="closePreview" class="px-8 py-3 bg-white border border-gray-200 text-gray-500 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all active:scale-95 shadow-sm">Tutup Preview</button>
                </div>
            </div>
        </div>
    @endif


    @push('scripts')
    <script>
        function confirmDeleteBankSoal(id, title) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus dari Bank?',
                    message: `Apakah Anda yakin ingin menghapus "${title}"? Soal yang dihapus tidak bisa dikembalikan, namun soal yang sudah dipakai di Ujian/Tugas tetap akan ada.`,
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteSoal',
                        params: [id]
                    }
                }
            }));
        }
    </script>
    @endpush
</div>
