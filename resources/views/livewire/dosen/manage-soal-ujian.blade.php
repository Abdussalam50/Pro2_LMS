<div class="min-h-screen bg-[#f8fafc] p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 md:mb-12">
            <div class="flex items-start md:items-center gap-4 md:gap-6">
                <a href="{{ route('dosen.classes') }}" class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5 md:w-6 md:h-6"></i>
                </a>
                <div class="min-w-0">
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight truncate">{{ $ujian->nama_ujian }}</h1>
                    <div class="flex flex-wrap items-center gap-2 md:gap-3 mt-1">
                        <span class="text-[9px] md:text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 md:px-3 py-1 rounded-full border border-indigo-100/50 whitespace-nowrap">Kelola Ujian</span>
                        <span class="hidden md:inline w-1 h-1 rounded-full bg-gray-300"></span>
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase tracking-widest truncate">{{ $ujian->mataKuliah->mata_kuliah }} - {{ $ujian->kelas->kelas }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex bg-white p-1 rounded-2xl border border-gray-100 shadow-sm">
                    <button wire:click="setTab('soals')" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'soals' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600' }}">
                        Daftar Soal
                    </button>
                    <button wire:click="setTab('materi')" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'materi' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-400 hover:text-gray-600' }}">
                        Materi Referensi
                    </button>
                </div>
                
                @if($activeTab === 'soals')
                    <button wire:click="openBankModal" class="group flex items-center gap-2 px-4 py-2 bg-white text-indigo-600 border border-indigo-100 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
                        <i data-lucide="database" class="w-4 h-4"></i>
                        Ambil dari Bank
                    </button>
                    <button wire:click="create" class="group flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95">
                        <div class="p-1.5 rounded-xl bg-white/10 group-hover:bg-white/20 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </div>
                        Tambah Soal
                    </button>
                @else
                    <button wire:click="openMateriModal" class="group flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-amber-700 transition-all shadow-xl shadow-amber-100 active:scale-95">
                        <div class="p-1.5 rounded-xl bg-white/10 group-hover:bg-white/20 transition-colors">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                        </div>
                        Unggah Materi
                    </button>
                @endif
            </div>
        </div>


        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">
            <div class="bg-white p-6 md:p-8 rounded-3xl md:rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center gap-4 md:gap-6">
                <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-[1.5rem] bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50 shrink-0">
                    <i data-lucide="help-circle" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Soal</p>
                    <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tighter">{{ $soalUjians->count() }}</h3>
                </div>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-3xl md:rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center gap-4 md:gap-6">
                <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-[1.5rem] bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50 shrink-0">
                    <i data-lucide="award" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Bobot</p>
                    <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tighter">{{ $soalUjians->sum('bobot') }}%</h3>
                </div>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-3xl md:rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center gap-4 md:gap-6 sm:col-span-2 lg:col-span-1">
                <div class="w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-[1.5rem] bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100/50 shrink-0">
                    <i data-lucide="clock" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Target Soal</p>
                    <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tighter">{{ $ujian->jumlah_soal }}</h3>
                </div>
            </div>
        </div>

        @if($activeTab === 'soals')
            <!-- Question List -->
            <div class="space-y-6">
                @forelse($soalUjians as $index => $soal)
                    <div wire:key="soal-{{ $soal->soal_id }}" class="bg-white rounded-[2rem] md:rounded-[3rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 hover:border-indigo-100 transition-all duration-300 overflow-hidden group">
                        <div class="p-5 md:p-8">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4 md:gap-6">
                                <div class="flex gap-4 md:gap-6 w-full">
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-gray-50 flex items-center justify-center text-[10px] md:text-[11px] font-black text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shrink-0">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-3 md:mb-4">
                                            <span class="px-3 py-1 rounded-full bg-gray-50 border border-gray-100 text-[9px] font-black text-gray-500 uppercase tracking-widest">
                                                {{ $soal->pilihan_ganda ? 'Pilihan Ganda' : 'Esai' }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-[9px] font-black text-indigo-600 uppercase tracking-widest">
                                                Bobot: {{ $soal->bobot }}
                                            </span>
                                        </div>
                                        <div class="text-gray-800 font-bold leading-relaxed text-lg prose max-w-none math-container">
                                            {!! $soal->soal !!}
                                        </div>

                                        @if($soal->pilihan_ganda)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-8">
                                                @foreach($soal->pilihan_ganda as $key => $value)
                                                    @if($value)
                                                        <div class="flex items-center gap-4 p-4 rounded-2xl border {{ $soal->jawaban_benar == $key ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-gray-50/50 border-gray-100 text-gray-600' }} math-container">
                                                            <div class="w-8 h-8 rounded-xl {{ $soal->jawaban_benar == $key ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400' }} flex items-center justify-center font-black text-[11px] uppercase shadow-sm">
                                                                {{ $key }}
                                                            </div>
                                                            <div class="text-sm font-medium prose prose-sm max-w-none">
                                                                {!! $value !!}
                                                            </div>
                                                            @if($soal->jawaban_benar == $key)
                                                                <i data-lucide="check" class="w-4 h-4 ml-auto"></i>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="mt-8 p-6 bg-amber-50 rounded-3xl border border-amber-100/50 math-container">
                                                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                    <i data-lucide="key" class="w-3 h-3"></i> Kunci Jawaban Esai
                                                </p>
                                                <div class="text-sm font-medium text-amber-800 italic prose prose-sm max-w-none">
                                                    {!! $soal->jawaban_esai ?: 'Tidak ada kunci jawaban.' !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex md:flex-col gap-2">
                                    <button wire:click="edit('{{ $soal->soal_id }}')" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all shadow-sm">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </button>
                                    <button onclick="confirmDeleteSoal('{{ $soal->soal_id }}', '{{ $index + 1 }}')" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-32 bg-gray-50/30 rounded-[4rem] border-2 border-dashed border-gray-100 opacity-60 my-4">
                        <div class="p-8 rounded-[2.5rem] bg-white shadow-xl border border-gray-50 mb-8 transform -rotate-6">
                            <i data-lucide="help-circle" class="w-16 h-16 text-gray-200 font-thin"></i>
                        </div>
                        <p class="font-black text-[11px] uppercase tracking-[0.3em] text-gray-400">Belum ada soal ditambahkan</p>
                    </div>
                @endforelse
            </div>
        @else
            <!-- Material List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($materiUjians as $materi)
                    <div wire:key="materi-{{ $materi->materi_id }}" class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-amber-500/5 hover:border-amber-100 transition-all duration-300 group relative overflow-hidden">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50 group-hover:bg-amber-600 group-hover:text-white transition-all duration-500">
                                <i data-lucide="file-text" class="w-7 h-7"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-gray-900 text-lg leading-tight truncate">{{ $materi->nama_materi }}</h4>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">PDF Document</p>
                            </div>
                        </div>
                        
                        @if($materi->deskripsi)
                            <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 mb-6">{{ $materi->deskripsi }}</p>
                        @endif

                        <div class="flex items-center gap-2 mt-auto">
                            <a href="{{ asset('storage/' . $materi->file_materi) }}" target="_blank" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 hover:text-white transition-all">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Lihat
                            </a>
                            <button onclick="confirmDeleteMateri('{{ $materi->materi_id }}', '{{ addslashes($materi->nama_materi) }}')" class="w-11 h-11 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>

                        <!-- Decor -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full -z-0"></div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-32 bg-gray-50/30 rounded-[4rem] border-2 border-dashed border-gray-100 opacity-60">
                        <div class="p-8 rounded-[2.5rem] bg-white shadow-xl border border-gray-50 mb-8 transform rotate-6">
                            <i data-lucide="file-text" class="w-16 h-16 text-gray-200 font-thin"></i>
                        </div>
                        <p class="font-black text-[11px] uppercase tracking-[0.3em] text-gray-400">Belum ada materi referensi</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    <!-- Soal Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white/95 backdrop-blur-xl p-8 rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-5xl border border-white/40 overflow-y-auto max-h-[90vh]">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $editMode ? 'Edit Soal' : 'Tambah Soal' }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Definisikan butir soal ujian berkala</p>
                    </div>
                </div>

                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Jenis Soal</label>
                            <select wire:model.live="form.type" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 appearance-none">
                                <option value="pilihan_ganda">Pilihan Ganda</option>
                                <option value="esai">Esai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot Soal</label>
                            <input type="number" wire:model="form.bobot" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: 5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Pertanyaan / Soal</label>
                        <div x-data="tinyEditor('form.soal')" wire:ignore>
                            <textarea x-ref="textarea" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 h-32" placeholder="Tuliskan detail soal di sini..."></textarea>
                        </div>
                        @error('form.soal') <span class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>

                    @if($form['type'] === 'pilihan_ganda')
                        <div class="space-y-4 pt-4 border-t border-gray-50">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Pilihan Jawaban</label>
                            
                            <div class="grid grid-cols-1 gap-3">
                                @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center font-black text-[11px] uppercase text-gray-400 shrink-0">
                                            {{ $opt }}
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <div x-data="tinyEditor('form.pilihan.{{ $opt }}', 200)" wire:ignore>
                                                <textarea x-ref="textarea" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20" placeholder="Ketik pilihan {{ strtoupper($opt) }}..."></textarea>
                                            </div>
                                            @error("form.pilihan.$opt") <span class="text-rose-500 text-[10px] font-bold pl-1">Pilihan {{ strtoupper($opt) }} wajib diisi.</span> @enderror
                                        </div>
                                        <label class="shrink-0 flex flex-col items-center justify-center gap-1 cursor-pointer group">
                                            <span class="text-[9px] font-black text-gray-400 group-hover:text-indigo-600 transition-colors uppercase tracking-widest">Kunci</span>
                                            <input type="radio" wire:model="form.jawaban_benar" value="{{ $opt }}" class="hidden peer">
                                            <div class="w-11 h-11 rounded-2xl border-2 border-gray-200 flex items-center justify-center peer-checked:border-emerald-500 peer-checked:bg-emerald-500 bg-white shadow-sm transition-all group-hover:border-indigo-300">
                                                <i data-lucide="check" class="w-5 h-5 text-white scale-0 peer-checked:scale-100 transition-transform"></i>
                                                <div class="w-2 h-2 rounded-full bg-gray-200 peer-checked:hidden"></div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('form.jawaban_benar') <div class="mt-2 text-rose-500 text-[10px] font-bold pl-1 flex items-center gap-1"><i data-lucide="alert-circle" class="w-3 h-3"></i> Mohon pilih satu jawaban yang benar sebagai kunci jawaban.</div> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kunci Jawaban / Kisi-kisi (Opsional)</label>
                            <div x-data="tinyEditor('form.jawaban_esai')" wire:ignore>
                                <textarea x-ref="textarea" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 h-24" placeholder="Pedoman penilaian esai..."></textarea>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4 sticky bottom-0 bg-white/100 pb-4">
                        <button type="button" wire:click="$set('showModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit" class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            {{ $editMode ? 'Update Soal' : 'Simpan Soal' }}
                            <i data-lucide="save" class="w-3.5 h-3.5 transition-transform group-hover:scale-110"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Materi Modal -->
    @if($showMateriModal)
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white/95 backdrop-blur-xl p-8 rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-md border border-white/40 overflow-y-auto max-h-[90vh]">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50">
                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Unggah Materi</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Lampirkan referensi pengerjaan ujian</p>
                    </div>
                </div>

                <form wire:submit.prevent="saveMateri" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Materi</label>
                        <input type="text" wire:model="materiForm.nama_materi" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: Modul Rumus Matematika">
                        @error('materiForm.nama_materi') <span class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Deskripsi Singkat</label>
                        <textarea wire:model="materiForm.deskripsi" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 h-24" placeholder="Keterangan materi..."></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">File Referensi (PDF)</label>
                        <div class="group relative px-4 py-8 border-2 border-dashed border-gray-100 rounded-[2rem] bg-gray-50/30 hover:bg-amber-50/30 hover:border-amber-200 transition-all text-center">
                            <input type="file" wire:model="materiForm.file_materi" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            <div class="space-y-2">
                                <i data-lucide="file-up" class="w-8 h-8 text-gray-300 mx-auto group-hover:text-amber-400 transition-colors mb-2"></i>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Klik atau tarik file ke sini</p>
                                <p class="text-[9px] text-gray-300">Maksimal 20MB (PDF/Docx)</p>
                            </div>
                        </div>
                        @error('materiForm.file_materi') <span class="text-rose-500 text-[10px] mt-1 font-bold pl-1">{{ $message }}</span> @enderror
                        
                        <div wire:loading wire:target="materiForm.file_materi" class="mt-2 text-[10px] font-black text-indigo-500 uppercase tracking-widest">
                            Mengunggah... mohon tunggu
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showMateriModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                        <button type="submit" class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            Upload Materi
                            <i data-lucide="check" class="w-3.5 h-3.5 transition-transform group-hover:scale-110"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Bank Soal Modal -->
    @if($showBankModal)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4 z-[9999] animate-in fade-in duration-300">
            <div class="bg-white/95 backdrop-blur-xl p-8 rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.2)] w-full max-w-4xl border border-white/40 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between mb-8 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100/50">
                            <i data-lucide="database" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Bank Soal Anda</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Pilih soal yang pernah Anda buat untuk digunakan kembali</p>
                        </div>
                    </div>
                    <button wire:click="$set('showBankModal', false)" class="text-gray-400 hover:text-rose-500 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 pr-2 space-y-4 custom-scrollbar">
                    @forelse($bankSoals as $bank)
                        <div wire:key="bank-{{ $bank->bank_soal_id }}" class="p-5 rounded-[2rem] border border-gray-100 bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 transition-all group flex gap-5 items-start">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 shrink-0 border border-gray-100">
                                <i data-lucide="{{ $bank->tipe_soal === 'pilihan_ganda' ? 'list' : 'align-left' }}" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[9px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $bank->tipe_soal) }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $bank->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="text-sm font-medium text-gray-800 line-clamp-2 prose prose-sm max-w-none math-container">
                                    {!! $bank->konten_soal !!}
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col gap-2 border-l border-gray-100 pl-5">
                                <button wire:click="importFromBank('{{ $bank->bank_soal_id }}')" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                    Gunakan Soal
                                </button>
                                <span class="text-center text-[10px] text-gray-400 font-bold uppercase tracking-widest">Bobot: {{ $bank->bobot_referensi }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16">
                            <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-300 mx-auto mb-4 border border-gray-100">
                                <i data-lucide="inbox" class="w-8 h-8"></i>
                            </div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Bank soal ujian masih kosong</p>
                            <p class="text-xs text-gray-400 mt-2">Soal yang Anda buat dari form "Tambah Soal" akan otomatis tersimpan di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        function confirmDeleteSoal(id, index) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Soal?',
                    message: `Apakah Anda yakin ingin menghapus soal nomor ${index}?`,
                    confirm: true,
                    confirmText: 'Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'delete',
                        params: [id]
                    }
                }
            }));
        }

        function confirmDeleteMateri(id, title) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Materi Referensi?',
                    message: `Apakah Anda yakin ingin menghapus materi "${title}"?`,
                    confirm: true,
                    confirmText: 'Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteMateri',
                        params: [id]
                    }
                }
            }));
        }
    </script>
    @endpush
</div>
