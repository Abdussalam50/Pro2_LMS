<div class="min-h-screen bg-[#f8fafc] p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 md:mb-12">
            <div class="flex items-start md:items-center gap-4 md:gap-6">
                <a href="{{ route('dosen.classes') }}" class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5 md:w-6 md:h-6"></i>
                </a>
                <div class="min-w-0">
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight truncate">Hasil Ujian: {{ $ujian->nama_ujian }}</h1>
                    <div class="flex flex-wrap items-center gap-2 md:gap-3 mt-1">
                        <span class="text-[9px] md:text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 md:px-3 py-1 rounded-full border border-indigo-100/50 whitespace-nowrap">Rekap Nilai</span>
                        <span class="hidden md:inline w-1 h-1 rounded-full bg-gray-300"></span>
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase tracking-widest truncate">{{ $ujian->mataKuliah->mata_kuliah }} - {{ $ujian->kelas->kelas }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List Table -->
        <div class="bg-white rounded-[1.5rem] md:rounded-[1.5rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em]">
                            <th class="p-6 md:p-8">Mahasiswa</th>
                            <th class="p-6 md:p-8">NIM</th>
                            <th class="p-6 md:p-8">Waktu Selesai</th>
                            <th class="p-6 md:p-8 text-center">Nilai</th>
                            <th class="p-6 md:p-8 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($hasilUjians as $hasil)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="p-6 md:p-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                            @if($hasil->mahasiswa->foto)
                                                <img src="{{ asset('storage/' . $hasil->mahasiswa->foto) }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="user" class="w-5 h-5 text-gray-300"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-gray-900 leading-tight">{{ $hasil->mahasiswa->nama }}</div>
                                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ $hasil->mahasiswa->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6 md:p-8">
                                    <span class="text-sm font-bold text-gray-600 tracking-tight">{{ $hasil->mahasiswa->nim }}</span>
                                </td>
                                <td class="p-6 md:p-8">
                                    <div class="text-xs font-bold text-gray-500">{{ $hasil->updated_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $hasil->updated_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="p-6 md:p-8 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl {{ $hasil->nilai >= 70 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} border {{ $hasil->nilai >= 70 ? 'border-emerald-100' : 'border-amber-100' }} font-black text-lg tracking-tighter shadow-sm">
                                        {{ $hasil->nilai ?? '-' }}
                                    </div>
                                </td>
                                <td class="p-6 md:p-8 text-center">
                                    <a href="{{ route('dosen.ujians.hasil.detail', [$ujian->ujian_id, $hasil->mahasiswa_id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                        Periksa
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-[2.5rem] bg-gray-50 flex items-center justify-center text-gray-200 mb-6">
                                            <i data-lucide="users" class="w-10 h-10"></i>
                                        </div>
                                        <p class="font-black text-[11px] uppercase tracking-[0.3em] text-gray-300">Belum ada mahasiswa yang selesai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Lucide handled globally
    </script>
</div>
