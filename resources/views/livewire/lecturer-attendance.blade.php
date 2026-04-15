<div class="p-0 md:p-6 max-w-8xl mx-auto">
    <!-- Header -->
    <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i data-lucide="clipboard-check" class="w-8 h-8"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen Presensi</h1>
                    <p class="text-gray-500 font-medium">Kelola kehadiran mahasiswa per pertemuan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Selection Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i data-lucide="filter" class="w-5 h-5 text-indigo-500"></i> Pilih Kelas & Sesi
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kelas</label>
                        <select wire:model.live="selectedClass" class="w-full border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 p-3 bg-gray-50 transition drop-shadow-sm">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Pertemuan</label>
                        <select wire:model.live="selectedMeeting" @disabled(empty($meetings)) class="w-full border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 p-3 bg-gray-50 disabled:opacity-50 transition drop-shadow-sm">
                            <option value="">-- Pilih Pertemuan --</option>
                            @foreach($meetings as $meeting)
                                <option value="{{ $meeting['id'] }}">{{ $meeting['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($selectedMeeting)
            <!-- Code Generator -->
            <div class="bg-gradient-to-br from-indigo-600 via-indigo-800 to-indigo-950 rounded-3xl p-8 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden ring-1 ring-white/10">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black flex items-center gap-2">
                            <i data-lucide="zap" class="w-6 h-6 text-yellow-400 fill-yellow-400/20"></i> Aktivasi
                        </h3>
                        @if($activeCode && $activeCode['is_active'])
                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-emerald-500/30">Aktif</span>
                        @endif
                    </div>
                    
                    @if($activeCode && $activeCode['is_active'])
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 mb-8 border border-white/10 text-center shadow-inner">
                            <p class="text-[10px] uppercase tracking-[0.3em] text-indigo-200 font-bold mb-3 opacity-70">Kode Presensi Anda</p>
                            <div class="text-5xl font-black tracking-widest font-mono text-transparent bg-clip-text bg-gradient-to-b from-white to-indigo-200 drop-shadow-sm select-all">
                                {{ $activeCode['code'] }}
                            </div>
                            <div class="mt-4 flex items-center justify-center gap-2 text-[10px] font-bold text-indigo-300/80">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                Berakhir: {{ \Carbon\Carbon::parse($activeCode['expires_at'])->format('H:i') }}
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button wire:click="generateCode('text')" class="flex-1 bg-white text-indigo-900 py-4 rounded-xl font-black text-sm hover:bg-indigo-50 transition transform active:scale-95 shadow-xl shadow-indigo-950/20">
                                RESET KODE
                            </button>
                            <button wire:click="toggleCode" class="group bg-red-500/20 hover:bg-red-500 text-red-500 hover:text-white p-4 rounded-xl transition border border-red-500/30 flex items-center justify-center">
                                <i data-lucide="power" class="w-6 h-6 group-active:scale-90 transition"></i>
                            </button>
                        </div>
                    @else
                        <div class="bg-indigo-950/40 rounded-2xl p-5 mb-6 border border-indigo-400/20">
                            <p class="text-sm text-indigo-100/90 leading-relaxed font-medium capitalize">Sesi presensi belum aktif. Mahasiswa tidak dapat melakukan absen sebelum Anda membuat kode.</p>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-2">Durasi Aktivasi (Menit)</label>
                            <div class="relative">
                                <i data-lucide="timer" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400"></i>
                                <input type="number" wire:model="duration" class="w-full bg-white/5 border-white/10 rounded-xl py-3 pl-11 text-white focus:ring-yellow-400 focus:border-yellow-400 text-sm font-bold shadow-inner" placeholder="Contoh: 60">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <button wire:click="generateCode('text')" class="flex flex-col items-center justify-center gap-3 bg-white/10 hover:bg-white/15 p-5 rounded-2xl transition border border-white/10 group hover:border-indigo-400/30 shadow-lg">
                                <div class="w-12 h-12 bg-yellow-400/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                    <i data-lucide="key-round" class="w-6 h-6 text-yellow-400"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest">Kombinasi</span>
                            </button>
                            <button wire:click="generateCode('qr')" class="flex flex-col items-center justify-center gap-3 bg-white/10 hover:bg-white/15 p-5 rounded-2xl transition border border-white/10 group hover:border-indigo-400/30 shadow-lg">
                                <div class="w-12 h-12 bg-emerald-400/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                    <i data-lucide="qr-code" class="w-6 h-6 text-emerald-400"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest">QR Scanner</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Daftar Kehadiran</h3>
                        <p class="text-xs text-gray-400">Daftar mahasiswa terdaftar di kelas ini</p>
                    </div>
                    @if($selectedMeeting)
                    <div class="flex items-center gap-2">
                         <span class="flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-600 rounded-full text-xs font-bold ring-1 ring-green-100">
                             <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                             Live Monitoring
                         </span>
                    </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    @if(!$selectedMeeting)
                        <div class="p-20 text-center text-gray-400">
                            <i data-lucide="user-plus" class="w-16 h-16 mx-auto mb-4 opacity-10"></i>
                            <p class="text-lg font-medium text-gray-300">Pilih kelas dan pertemuan untuk melihat data presensi</p>
                        </div>
                    @else
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Metode</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($attendanceRecords as $record)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-black text-xs">
                                                {{ substr($record['name'], 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ $record['name'] }}</p>
                                                <p class="text-xs text-gray-400">{{ $record['nim'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {{ strtoupper($record['metode']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-600">
                                        {{ $record['waktu'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($record['status'] === 'hadir')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">HADIR</span>
                                        @elseif($record['status'] === 'alfa')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700 ring-1 ring-red-200">ALFA</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-400">BELUM</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">Tidak ada mahasiswa terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- QR Modal -->
    <div x-show="$wire.showQR" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-indigo-950/80 backdrop-blur-md" @click="$wire.showQR = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl p-10 max-w-sm w-full text-center">
            <h3 class="text-2xl font-black text-gray-800 mb-2">Scan QR Presensi</h3>
            <p class="text-gray-500 mb-6 font-medium">Pertemuan {{ $selectedMeeting }}</p>
            
            <div class="bg-gray-50 p-6 rounded-2xl border-4 border-gray-100 inline-block mb-8">
                <img src="{{ $qrUrl }}" alt="Attendance QR Code" class="w-full">
            </div>

            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 mb-8">
                <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Backup Kode</p>
                <div class="text-3xl font-black font-mono tracking-widest text-indigo-900">{{ $activeCode['code'] ?? '' }}</div>
            </div>

            <button @click="$wire.showQR = false" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition active:scale-95 uppercase tracking-widest">
                Tutup QR
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
             setInterval(() => {
                 if(@this.selectedMeeting) {
                     @this.loadAttendanceData();
                 }
             }, 5000);

             Livewire.hook('morph.updated', ({ el, component }) => {
                // Lucide is now handled globally in app.js
            });
        });
    </script>
</div>
