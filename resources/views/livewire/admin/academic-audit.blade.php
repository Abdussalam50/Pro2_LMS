<div class="py-6">
    <x-slot name="title">Audit Pelaksanaan Perkuliahan - Admin</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center">
                        <i data-lucide="activity" class="w-3 h-3 mr-1.5"></i> Live Monitoring
                    </span>
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none uppercase italic">Audit Akademik</h1>
                <p class="text-gray-500 mt-3 font-medium max-w-2xl text-lg">Pantau kemajuan pertemuan dan kehadiran mahasiswa di seluruh program studi secara real-time.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[240px]">
                    <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10">Pilih Semester</label>
                    <select wire:model.live="selectedPeriodId" class="block w-full rounded-2xl border-gray-200 bg-white shadow-sm focus:border-indigo-500 font-bold h-14 pl-4 pr-10 appearance-none">
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <i data-lucide="chevron-down" class="h-5 w-5 text-gray-400"></i>
                    </div>
                </div>

                <button wire:click="exportCsv" class="h-14 px-8 inline-flex items-center justify-center border-2 border-emerald-600/20 text-emerald-700 text-sm font-black rounded-2xl hover:bg-emerald-50 transition-all active:scale-95 group">
                    <i data-lucide="file-spreadsheet" class="mr-2 h-5 w-5 group-hover:scale-110 transition-transform"></i>
                    Export Excel
                </button>

                <button onclick="window.print()" class="h-14 px-8 inline-flex items-center justify-center border-2 border-gray-900/5 text-gray-900 text-sm font-black rounded-2xl hover:bg-gray-100 transition-all active:scale-95 print:hidden">
                    <i data-lucide="printer" class="mr-2 h-5 w-5"></i>
                    Cetak PDF
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 mb-8 flex items-center print:hidden">
            <i data-lucide="search" class="w-6 h-6 text-gray-300 ml-4"></i>
            <input type="text" wire:model.live="search" placeholder="Cari Mata Kuliah, Dosen, atau Kode Kelas..." class="w-full border-none focus:ring-0 font-bold text-gray-900 h-10 px-4 placeholder-gray-300 text-lg">
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">#</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 min-w-[300px]">Detail Kelas & Kurikulum</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Kemajuan Pertemuan</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Rerata Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 whitespace-nowrap">
                    @forelse($auditData as $index => $row)
                        <tr class="group hover:bg-indigo-50/10 transition-all duration-300">
                            <td class="px-8 py-6 text-sm font-black text-gray-300 group-hover:text-indigo-400 transition-colors">{{ $index + 1 }}</td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="text-lg font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $row['mata_kuliah'] }}</span>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black bg-gray-100 text-gray-600 group-hover:bg-indigo-100 group-hover:text-indigo-700 transition-all">{{ $row['kelas'] }}</span>
                                        <span class="text-xs font-bold text-gray-400 flex items-center">
                                            <i data-lucide="user" class="w-3 w-3 mr-1 text-gray-300"></i> {{ $row['dosen'] }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 min-w-[240px]">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-black text-gray-900 uppercase">Pertemuan {{ $row['completed_meetings'] }}/16</span>
                                        <span class="text-xs font-black text-indigo-600">{{ $row['progress'] }}%</span>
                                    </div>
                                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden flex">
                                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000 ease-out shadow-sm" style="width: {{ $row['progress'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col items-center">
                                    <div @class([
                                        'text-2xl font-black mb-0.5',
                                        'text-emerald-500' => $row['avg_attendance'] >= 80,
                                        'text-amber-500' => $row['avg_attendance'] >= 60 && $row['avg_attendance'] < 80,
                                        'text-rose-500' => $row['avg_attendance'] < 60,
                                    ])>
                                        {{ $row['avg_attendance'] }}%
                                    </div>
                                    <div class="flex gap-0.5">
                                        @for($i=0; $i<5; $i++)
                                            <div class="w-3 h-1 rounded-full {{ ($row['avg_attendance']/20) > $i ? ($row['avg_attendance'] >= 80 ? 'bg-emerald-500' : ($row['avg_attendance'] >= 60 ? 'bg-amber-500' : 'bg-rose-500')) : 'bg-gray-100' }}"></div>
                                        @endfor
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="clipboard-list" class="w-10 h-10 text-gray-200"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900">Belum Ada Data Audit</h3>
                                    <p class="text-gray-400 mt-1 font-bold">Data kelas di semester ini belum tersedia atau dosen belum memulai pertemuan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Only Footer -->
    <div class="hidden print:block mt-12 pt-8 border-t border-gray-200">
        <div class="flex justify-between items-end text-[10px] font-black uppercase tracking-widest text-gray-400">
            <div>Dicetak oleh: {{ auth()->user()->name }} (ADMIN)</div>
            <div>Halaman 1 dari 1</div>
            <div>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>
</div>
