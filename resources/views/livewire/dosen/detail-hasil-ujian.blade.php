<div class="min-h-screen bg-[#f8fafc] pb-32">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 md:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dosen.ujians.hasil', $ujian->ujian_id) }}" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-all">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-sm font-black text-gray-900 leading-none">Periksa Jawaban</h1>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">{{ $mahasiswa->nama }} - {{ $mahasiswa->nim }}</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-right">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Total Nilai</p>
                    <div class="text-xl font-black text-indigo-600 tracking-tighter">{{ $nilai->nilai ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-5xl mx-auto px-4 md:px-6 mt-8 space-y-8">
        @foreach($jawabans as $index => $j)
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8">
                    <div class="flex gap-6 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-[10px] font-black text-gray-400 shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <div class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-2">Pertanyaan:</div>
                            <div class="text-gray-800 font-bold leading-relaxed prose max-w-none math-container">
                                {!! $j->soal->soal !!}
                            </div>
                        </div>
                    </div>

                    <div class="md:ml-16 pt-6 border-t border-gray-50">
                        <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3">Jawaban Mahasiswa:</div>
                        <div class="p-6 bg-gray-50/50 rounded-2xl border border-gray-100 text-sm font-medium text-gray-700 math-container">
                            {!! ($j->jawaban_pilihan_ganda ?? $j->jawaban_esai) ?: '<span class="italic text-gray-400">Tidak ada jawaban.</span>' !!}
                        </div>

                        @if($j->soal->pilihan_ganda)
                            <div class="mt-4 flex items-center gap-3">
                                <div class="px-3 py-1 rounded-full {{ $j->is_benar ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }} text-[9px] font-black uppercase tracking-widest">
                                    {{ $j->is_benar ? 'Benar' : 'Salah' }}
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">Skor: {{ $j->skor }}</span>
                            </div>
                        @else
                            <div class="mt-6 space-y-4">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                    <div>
                                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Berikan Skor (0 - {{ $j->soal->bobot }})</p>
                                        <p class="text-[9px] text-indigo-400 font-bold">Kunci: {!! strip_tags($j->soal->jawaban_esai) !!}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="number" wire:model.lazy="grades.{{ $j->jawaban_id }}" max="{{ $j->soal->bobot }}" class="w-24 md:w-32 px-4 py-2 bg-white border border-indigo-100 rounded-xl text-center font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500/20">
                                        <button wire:click="saveGrade('{{ $j->jawaban_id }}')" class="flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 whitespace-nowrap">
                                            <i data-lucide="save" class="w-4 h-4"></i> Simpan
                                        </button>
                                    </div>
                                </div>

                                <!-- AI Review Section for Exam Essay -->
                                @if(isset($aiReviews[$j->jawaban_id]))
                                    <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-sm animate-in fade-in zoom-in-95 duration-300">
                                        <div class="flex items-start gap-4">
                                            <div class="bg-indigo-600 p-2.5 rounded-xl text-white shrink-0">
                                                <i data-lucide="bot" class="w-5 h-5"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                        Saran AI Korektor
                                                    </span>
                                                    @if(!isset($aiReviews[$j->jawaban_id]['error']))
                                                        <span class="text-[11px] font-black text-indigo-700">Skor: {{ $aiReviews[$j->jawaban_id]['suggested_score'] }}</span>
                                                    @endif
                                                </div>
                                                @if(isset($aiReviews[$j->jawaban_id]['error']))
                                                    <p class="text-[11px] text-rose-500 font-bold italic">{{ $aiReviews[$j->jawaban_id]['error'] }}</p>
                                                @else
                                                    <p class="text-xs text-gray-600 font-medium leading-relaxed">{{ $aiReviews[$j->jawaban_id]['feedback'] }}</p>
                                                    <button wire:click="applyAiReview('{{ $j->jawaban_id }}')" class="mt-3 text-[10px] font-black text-indigo-600 hover:text-indigo-700 uppercase tracking-[0.2em] flex items-center gap-2 transition-all group">
                                                        <i data-lucide="check-circle" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                                                        Gunakan Saran & Simpan
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <button 
                                        wire:click="getAiReviewForExam('{{ $j->jawaban_id }}')" 
                                        wire:loading.attr="disabled"
                                        class="w-full py-3 px-6 rounded-2xl border border-dashed border-indigo-200 text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50/50 hover:border-indigo-400 transition-all flex items-center justify-center gap-2 group disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="getAiReviewForExam('{{ $j->jawaban_id }}')">
                                            <i data-lucide="sparkles" class="w-4 h-4 group-hover:scale-110 group-hover:rotate-12 transition-transform"></i>
                                            Minta Penilaian AI
                                        </span>
                                        <span wire:loading wire:target="getAiReviewForExam('{{ $j->jawaban_id }}')" class="flex items-center gap-2">
                                            <svg class="animate-spin h-3.5 w-3.5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            AI Sedang Mengoreksi...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Lucide handled globally
    </script>
</div>
