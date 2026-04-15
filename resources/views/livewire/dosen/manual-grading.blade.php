<div class="px-4 py-8 max-w-7xl mx-auto space-y-6 animate-in slide-in-from-bottom-4 duration-500">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('dosen.ujians.hasil', $ujian->ujian_id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Manajemen
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $ujian->nama_ujian }}</h1>
            <p class="text-gray-500 mt-1 flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Entri Manual / Rubrik Subjektif</span>
                &bull; {{ $ujian->kelas->kelas }} ({{ $ujian->mataKuliah->mata_kuliah }})
            </p>
        </div>
        <div>
            <button wire:click="saveAll" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span wire:loading.remove wire:target="saveAll">Simpan Semua Nilai</span>
                <span wire:loading wire:target="saveAll">Menyimpan...</span>
            </button>
        </div>
    </div>

    <!-- Alert Instruksi -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Ini adalah halaman Entri Nilai Manual. Gunakan kolom input di bawah ini untuk memasukkan nilai final mahasiswa (skala 0-100). Sangat cocok untuk komponen seperti Keaktifan, Kinerja Praktikum, atau Penilaian Projek Akhir yang tidak menggunakan butir soal.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap gap-4 items-center justify-between">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" class="form-input block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Cari nama atau NIM..." type="search">
            </div>
            <div class="text-sm text-gray-500 font-medium whitespace-nowrap">
                Menampilkan {{ count($students) }} Mahasiswa
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">
                            IDENTITAS MAHASISWA
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">
                            NILAI AKHIR (0-100)
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-sm">
                                        {{ substr($student['name'], 0, 2) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $student['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $student['mahasiswa']['nim'] ?? 'Tidak ada NIM' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative max-w-[120px]">
                                <input type="number" step="0.01" min="0" max="100" 
                                       wire:model="scores.{{ $student['id'] }}" 
                                       class="form-input block w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold text-gray-900"
                                       placeholder="-" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <p class="text-lg font-medium">Tidak ada mahasiswa yang ditemukan</p>
                            @if($search)
                                <p class="text-sm mt-1">Pencarian "{{ $search }}" tidak cocok dengan data manapun.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
