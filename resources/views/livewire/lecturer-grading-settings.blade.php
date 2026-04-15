<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    Pengaturan Komponen Nilai
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Atur komponen penilaian dan bobotnya untuk kelas <span class="font-medium text-primary-600">{{ $kelas->nama_kelas }}</span>. Total harus 100%.
                </p>
            </div>
            <button wire:click="openAddModal" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Komponen
            </button>
        </div>

        <div class="p-6">
            <!-- Alert for Total Weight -->
            <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $totalWeight == 100 ? 'bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800' : 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800' }}">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full {{ $totalWeight == 100 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        @if($totalWeight == 100)
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        @endif
                    </div>
                    <div>
                        <h4 class="font-medium">Total Bobot: {{ $totalWeight }}%</h4>
                        <p class="text-sm opacity-80">
                            @if($totalWeight == 100)
                                Total bobot sudah sesuai 100%. Anda bisa menyimpannya.
                            @elseif($totalWeight > 100)
                                Total bobot melebihi 100%. Kurangi {{ $totalWeight - 100 }}% lagi.
                            @else
                                Total bobot kurang dari 100%. Tambahkan {{ 100 - $totalWeight }}% lagi.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- List Components -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Komponen</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Bobot (%)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($components as $index => $comp)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $comp['name'] }}
                                    </div>
                                    @if($comp['is_default'])
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Bawaan
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeLabels = [
                                        'assignment' => ['Materi/Sintaks (Tugas)', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200'],
                                        'exam' => ['Kuis/Ujian Terjadwal', 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'],
                                        'attendance' => ['Presensi/Kehadiran', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200'],
                                        'manual' => ['Entri Manual Dosen', 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'],
                                    ];
                                    $label = $typeLabels[$comp['mapping_type']][0] ?? $comp['mapping_type'];
                                    $color = $typeLabels[$comp['mapping_type']][1] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" min="0" max="100" wire:model.live.debounce.500ms="components.{{ $index }}.weight" 
                                           class="form-input block w-full sm:text-sm sm:leading-5 @error('components.'.$index.'.weight') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                                </div>
                                @error('components.'.$index.'.weight')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(!$comp['is_default'])
                                <button wire:click="deleteComponent('{{ $comp['id'] }}')" 
                                        wire:confirm="Yakin ingin menghapus komponen {{ $comp['name'] }}?"
                                        class="text-red-600 hover:text-red-900 transition-colors p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="saveWeights" 
                        @if($totalWeight != 100) disabled @endif
                        class="px-4 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-[0.1em] rounded-2xl transition-all shadow-xl shadow-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    <span wire:loading.remove wire:target="saveWeights">Simpan Pengaturan</span>
                    <span wire:loading wire:target="saveWeights">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Komponen -->
    @if($showAddModal)
        @teleport('body')
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background: rgba(17,24,39,0.6); backdrop-filter: blur(4px);">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl border border-gray-100 overflow-hidden">
                <div class="p-8 md:p-10">
                    <!-- Header -->
                    <div class="flex items-center justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 tracking-tight">Tambah Komponen Nilai</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Atur kategori bobot baru</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('showAddModal', false)" class="text-gray-400 hover:text-gray-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Komponen</label>
                            <input type="text" wire:model="newName" 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" 
                                   placeholder="Cth: Projek Akhir, Keaktifan, Praktikum">
                            @error('newName') <span class="text-xs text-rose-500 mt-1 pl-1 font-bold">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Tipe Pelaksanaan</label>
                            <select wire:model="newMappingType" 
                                    class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="exam">Kuis / Ujian Esai (Peserta mengerjakan online)</option>
                                <option value="manual">Entri Manual (Dosen input nilai langsung)</option>
                                <option value="assignment">Tugas Sintaks (Di dalam materi/flow builder)</option>
                                <option value="attendance">Kehadiran / Presensi</option>
                            </select>
                            <p class="mt-2 text-[10px] text-gray-400 italic pl-1 font-medium">Tipe manual cocok untuk formasi nilai yang diinput dosen secara sepihak.</p>
                            @error('newMappingType') <span class="text-xs text-rose-500 mt-1 pl-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Bobot Persentase (%)</label>
                            <input type="number" min="0" max="100" wire:model="newWeight" 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                   placeholder="0">
                            @error('newWeight') <span class="text-xs text-rose-500 mt-1 pl-1 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-8 border-t border-gray-100 mt-8">
                        <button type="button" wire:click="$set('showAddModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">
                            Batal
                        </button>
                        <button type="button" wire:click="addComponent" class="group flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                            Simpan Komponen
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>
