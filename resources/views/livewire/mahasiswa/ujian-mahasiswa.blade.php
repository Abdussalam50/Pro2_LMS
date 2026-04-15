<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Jadwal Ujian</h1>
        <p class="text-sm text-gray-500 font-medium">Lihat dan ikuti ujian yang tersedia untuk kelas Anda.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($ujians as $ujian)
            @php
                $isStarted = now()->isAfter($ujian->waktu_mulai);
                $isEnded = now()->isAfter($ujian->waktu_selesai);
                $hasSubmitted = $ujian->has_submitted;
                $canTake = $isStarted && !$isEnded && $ujian->is_open && !$hasSubmitted;
            @endphp
            <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:border-indigo-100 transition-all group relative overflow-hidden">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-2">
                        <div class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest border border-indigo-100/50">
                            {{ $ujian->jenis_ujian }}
                        </div>
                        <div class="px-3 py-1 flex items-center gap-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm
                            {{ $ujian->mode_batasan === 'strict' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 
                              ($ujian->mode_batasan === 'materi_only' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-white text-gray-500 border border-gray-200') }}">
                            @if($ujian->mode_batasan === 'strict')
                                <i data-lucide="lock" class="w-3 h-3"></i> Ketat
                            @elseif($ujian->mode_batasan === 'materi_only')
                                <i data-lucide="book-open" class="w-3 h-3"></i> Buka Materi
                            @else
                                <i data-lucide="globe" class="w-3 h-3"></i> Terbuka
                            @endif
                        </div>
                    </div>
                    @if($hasSubmitted)
                        <span class="text-[10px] font-black text-green-600 uppercase tracking-widest flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3"></i> Sudah Dikerjakan
                        </span>
                    @elseif($isEnded)
                        <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">Selesai</span>
                    @elseif($isStarted)
                        <span class="text-[10px] font-black text-green-500 uppercase tracking-widest animate-pulse">Sedang Berlangsung</span>
                    @else
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Belum Mulai</span>
                    @endif
                </div>

                <h3 class="text-xl font-black text-gray-900 mb-2 leading-tight group-hover:text-indigo-600 transition-colors">{{ $ujian->nama_ujian }}</h3>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-4">{{ $ujian->mataKuliah->mata_kuliah }}</p>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium">{{ $ujian->waktu_mulai->format('d M, H:i') }} - {{ $ujian->waktu_selesai->format('H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium">{{ $ujian->soalUjians->count() }} Soal</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-xs font-medium">{{ $ujian->dosen->nama }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Bobot: {{ $ujian->bobot_nilai }}%
                    </div>
                    @if($hasSubmitted)
                        <button disabled class="px-5 py-2 bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest rounded-xl cursor-not-allowed border border-green-100 flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-3 h-3"></i> Selesai
                        </button>
                    @elseif($canTake)
                        <a href="{{ route('mahasiswa.ujians.take', $ujian->ujian_id) }}" wire:navigate class="px-5 py-2 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 inline-block">
                            Ikuti Ujian
                        </a>
                    @else
                        <button disabled class="px-5 py-2 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-xl cursor-not-allowed border border-gray-100 italic">
                            @if($isEnded)
                                Waktu Habis
                            @elseif(!$ujian->is_open && $isStarted)
                                Belum Dibuka
                            @else
                                Belum Mulai
                            @endif
                        </button>
                    @endif
                </div>

                <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-indigo-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-gray-50/50 rounded-[3rem] border-2 border-dashed border-gray-100">
                <p class="text-[11px] font-black text-gray-300 uppercase tracking-[0.3em]">Tidak ada ujian aktif saat ini</p>
            </div>
        @endforelse
    </div>
</div>
