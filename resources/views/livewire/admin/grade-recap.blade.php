<div class="py-6">
    <x-slot name="title">Rekapitulasi Nilai & Penilaian - Admin/Dosen</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Control Header -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-indigo-100/50 border border-indigo-50 p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                        <i data-lucide="award" class="w-10 h-10 text-indigo-600"></i>
                        Rekapitulasi Nilai
                    </h1>
                    <p class="text-gray-500 mt-2 font-medium">Lihat perolehan nilai mahasiswa berdasarkan bobot periode yang ditentukan admin.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4">
                    <button wire:click="exportCsv" class="h-12 px-6 inline-flex items-center justify-center bg-emerald-600 text-white text-sm font-black rounded-xl hover:bg-emerald-700 transition-all active:scale-95 shadow-lg shadow-emerald-100">
                        <i data-lucide="download" class="mr-2 h-4 w-4"></i> Export CSV
                    </button>
                    <button onclick="window.print()" class="h-12 px-6 inline-flex items-center justify-center border-2 border-gray-100 text-gray-900 text-sm font-black rounded-xl hover:bg-gray-50 transition-all active:scale-95 print:hidden">
                        <i data-lucide="printer" class="mr-2 h-4 w-4"></i> Cetak PDF
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10">Periode Akademik</label>
                    <select wire:model.live="selectedPeriodId" class="block w-full rounded-2xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 font-bold h-14 pl-4 pr-10 appearance-none">
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10">Pilih Kelas</label>
                    <select wire:model.live="selectedClassId" class="block w-full rounded-2xl border-gray-100 bg-gray-50/50 shadow-sm focus:border-indigo-500 font-bold h-14 pl-4 pr-10 appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($this->classes as $kelas)
                            <option value="{{ $kelas->kelas_id }}">{{ $kelas->mataKuliah->mata_kuliah }} ({{ $kelas->kelas }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10">Cari Mahasiswa</label>
                    <div class="flex items-center bg-gray-50/50 rounded-2xl border border-gray-100 h-14 px-4 shadow-sm">
                        <i data-lucide="search" class="w-5 h-5 text-gray-300 mr-3"></i>
                        <input type="text" wire:model.live="search" placeholder="Nama atau NIM..." class="w-full border-none bg-transparent focus:ring-0 font-bold text-gray-900 placeholder-gray-300">
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Table -->
        @if($selectedClassId)
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden print:shadow-none print:border-none">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Identitas Mahasiswa</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Avg Tugas</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">UTS</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">UAS</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-indigo-500 text-center bg-indigo-50/50">Nilai Akhir</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 italic font-bold">
                        @forelse($gradeData as $row)
                            <tr class="hover:bg-gray-50/50 transition-all duration-200">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-900 mb-0.5">{{ $row['name'] }}</span>
                                        <span class="text-[10px] font-black text-indigo-500 tracking-widest uppercase">{{ $row['nim'] }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center text-gray-600 font-bold text-sm">{{ $row['tugas'] }}</td>
                                <td class="px-8 py-6 text-center text-gray-600 font-bold text-sm">{{ $row['uts'] }}</td>
                                <td class="px-8 py-6 text-center text-gray-600 font-bold text-sm">{{ $row['uas'] }}</td>
                                <td class="px-8 py-6 text-center bg-indigo-50/30">
                                    <span class="text-lg font-black text-indigo-600">{{ $row['akhir'] }}</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span @class([
                                        'px-3 py-1 rounded-lg text-xs font-black uppercase',
                                        'bg-emerald-100 text-emerald-700' => in_array($row['huruf'], ['A', 'B']),
                                        'bg-amber-100 text-amber-700' => $row['huruf'] == 'C',
                                        'bg-rose-100 text-rose-700' => in_array($row['huruf'], ['D', 'E']),
                                    ])>
                                        {{ $row['huruf'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center text-gray-400 font-bold">Mahasiswa belum ditemukan atau belum ada nilai masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-indigo-50 rounded-[2rem] p-12 text-center border-2 border-dashed border-indigo-200">
                <i data-lucide="info" class="w-16 h-16 text-indigo-300 mx-auto mb-4"></i>
                <h3 class="text-2xl font-black text-indigo-900">Pilih Kelas Terlebih Dahulu</h3>
                <p class="text-indigo-600 mt-2 font-medium">Anda perlu memilih kelas untuk memproses rekapitulasi nilai mahasiswa.</p>
            </div>
        @endif
    </div>

    <!-- Media Print Optimization -->
    <style>
        @media print {
            .print\:hidden { display: none !important; }
            body { background: white !important; }
            .shadow-xl, .shadow-sm { box-shadow: none !important; }
            .bg-indigo-50\/30 { background-color: rgba(238, 242, 255, 0.3) !important; }
            .text-indigo-600 { color: #4f46e5 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            tr { border-bottom: 1px solid #e5e7eb !important; page-break-inside: avoid; }
        }
    </style>
</div>
