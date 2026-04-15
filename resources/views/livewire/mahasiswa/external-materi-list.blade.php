<div class="py-10 px-4 md:px-0">
    <div class="mb-10 text-center max-w-3xl mx-auto">
        <h1 class="text-4xl font-black text-gray-900 mb-4 tracking-tight">Rekomendasi <span class="text-indigo-600">Materi Eksternal</span></h1>
        <p class="text-lg text-gray-600 leading-relaxed">Kumpulan referensi tambahan pilihan dosen untuk mendukung proses belajarmu di luar kelas.</p>
    </div>

    @if($materiList->isEmpty())
        <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 p-16 text-center border border-gray-100 max-w-2xl mx-auto transition-all hover:shadow-2xl">
            <div class="relative w-32 h-32 mx-auto mb-8">
                <div class="absolute inset-0 bg-indigo-50 rounded-full animate-pulse"></div>
                <div class="relative flex items-center justify-center w-full h-full">
                    <i data-lucide="library-big" class="w-16 h-16 text-indigo-200"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Referensi</h3>
            <p class="text-gray-500 max-w-sm mx-auto">Dosen pengampu belum menambahkan materi eksternal untuk kelasmu saat ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($materiList as $materi)
                <div class="group bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 hover:shadow-2xl hover:shadow-indigo-100/50 p-6 transition-all duration-500 hover:-translate-y-2 flex flex-col h-full relative overflow-hidden">
                    <!-- Decorative element -->
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 transition-transform duration-500 group-hover:rotate-6">
                                    <i data-lucide="book-open-check" class="w-6 h-6"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full border border-indigo-100">{{ $materi->mataKuliah?->mata_kuliah ?? 'UMUM' }}</span>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors">{{ $materi->judul }}</h3>
                        
                        <p class="text-gray-600 text-sm mb-8 line-clamp-3 leading-relaxed">
                            {{ $materi->deskripsi }}
                        </p>

                        <div class="mt-auto pt-6 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-gray-400">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="text-xs font-semibold">{{ $materi->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($materi->link) }}" target="_blank" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-md shadow-indigo-200 hover:bg-indigo-700 hover:shadow-lg transition-all duration-300 active:scale-95 group/btn">
                                <span>Buka Materi</span>
                                <i data-lucide="external-link" class="w-4 h-4 transition-transform group-hover/btn:translate-x-1 group-hover/btn:-translate-y-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
