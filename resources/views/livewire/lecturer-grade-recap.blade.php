<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                <i data-lucide="bar-chart" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Rekapitulasi Nilai</h1>
                <p class="text-sm text-gray-500 mt-1">Lihat dan pantau nilai mahasiswa berdasarkan mata kuliah, kelas, dan pertemuan.</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Filter Mata Kuliah -->
            <div>
                <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Mata Kuliah</label>
                <select 
                    id="course" 
                    wire:model.live="selectedCourseId"
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer"
                >
                    <option value="">-- Pilih Mata Kuliah --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course['mata_kuliah_id'] }}">{{ $course['nama_mata_kuliah'] ?? $course['mata_kuliah'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label for="class" class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                <select 
                    id="class" 
                    wire:model.live="selectedClassId"
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer"
                    {{ empty($classes) ? 'disabled' : '' }}
                >
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas['kelas_id'] }}">{{ $kelas['kelas'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Pertemuan -->
            <div>
                <label for="meeting" class="block text-sm font-medium text-gray-700 mb-2">Pertemuan</label>
                <select 
                    id="meeting" 
                    wire:model.live="selectedMeetingId"
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer"
                    {{ empty($meetings) ? 'disabled' : '' }}
                >
                    <option value="">-- Pilih Pertemuan --</option>
                    @foreach($meetings as $meeting)
                        <option value="{{ $meeting['pertemuan_id'] }}">{{ $meeting['pertemuan'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    @if($selectedClassId && empty($students))
        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500">
            <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 text-gray-400"></i>
            <p>Belum ada mahasiswa terdaftar atau nilai yang masuk untuk kelas ini.</p>
        </div>
    @elseif($selectedClassId && !empty($students))
        <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50 overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Rekapitulasi Nilai Tertimbang</h2>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Berdasarkan konfigurasi bobot kelas</p>
                </div>
                <div class="flex gap-2">
                    @foreach($activeComponents as $comp)
                        @if($comp['weight'] > 0)
                            <div class="px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-xl flex flex-col items-center">
                                <span class="text-[8px] font-black text-indigo-400 uppercase tracking-tighter">{{ $comp['name'] }}</span>
                                <span class="text-xs font-black text-indigo-700">{{ $comp['weight'] }}%</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Mahasiswa</th>
                            @foreach($activeComponents as $comp)
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">{{ $comp['name'] }}</th>
                            @endforeach
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-indigo-600 text-center bg-indigo-50/30">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($students as $student)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 font-black text-xs border border-gray-200 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-500 transition-all">
                                            {{ substr($student['name'], 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $student['name'] }}</div>
                                            <div class="text-[10px] text-gray-400 font-bold font-mono">{{ $student['nim'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach($activeComponents as $comp)
                                    @php 
                                        $avg = $student['averages'][$comp['id']]['score'] ?? 0;
                                        $weighted = ($avg * ($comp['weight'] / 100));
                                    @endphp
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-black {{ $weighted > 0 ? 'text-indigo-600' : 'text-gray-300' }}">
                                                {{ number_format($weighted, 2) }}
                                            </span>
                                            <div class="flex items-center gap-1 opacity-50">
                                                <span class="text-[8px] font-black text-gray-400 uppercase tracking-tighter">Skor: {{ number_format($avg, 1) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                @endforeach

                                <td class="px-6 py-4 text-center bg-indigo-50/20">
                                    <div class="inline-flex flex-col items-center p-2 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 min-w-[60px]">
                                        <span class="text-lg font-black leading-none">{{ number_format($finalGrades[$student['id']] ?? 0, 1) }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-widest opacity-70">Final</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100 text-[10px] font-bold text-gray-400 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <span>Total Mahasiswa: <strong class="text-gray-900">{{ count($students) }}</strong></span>
                    <span class="flex items-center gap-1"><i data-lucide="info" class="w-3 h-3 text-indigo-400"></i> Nilai akhir dihitung dengan rumus rata-rata tertimbang.</span>
                </div>
                <button wire:click="refreshData" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Segarkan Data
                </button>
            </div>
        </div>
    @else
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-md font-medium text-indigo-900">Pilih Filter</h3>
                <p class="text-sm text-indigo-700 mt-1">Silakan pilih Mata Kuliah, Kelas, dan Pertemuan pada filter di atas untuk melihat rekapitulasi nilai mahasiswa.</p>
            </div>
        </div>
    @endif
</div>
