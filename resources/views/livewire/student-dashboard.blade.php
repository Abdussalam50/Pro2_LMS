@php $user = auth()->user(); @endphp

<div class="max-w-7xl mx-auto pb-10 space-y-8">

    {{-- Welcome Header --}}
    <div class="relative bg-gradient-to-r from-indigo-800 to-indigo-900 rounded-2xl p-8 overflow-hidden shadow-lg border border-indigo-700">
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 20% 50%, white 0%, transparent 50%), radial-gradient(circle at 80% 20%, white 0%, transparent 40%)"></div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <p class="text-indigo-200 text-sm font-medium mb-1">Selamat datang kembali 👋</p>
                <h1 class="text-3xl font-black text-white">{{ $user->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-indigo-200">
                    <p class="text-sm italic opacity-80">Mahasiswa</p>
                    <span class="w-1 h-1 rounded-full bg-indigo-400"></span>
                    <div class="relative">
                        <select wire:model.live="selectedPeriod" class="bg-white/10 border-none text-indigo-100 text-[10px] font-black uppercase tracking-widest rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-white/20 appearance-none pr-8 cursor-pointer hover:bg-white/20 transition-all font-sans">
                            <option value="active" class="text-gray-900">Semester Berjalan ({{ $activePeriodData?->name ?? 'None' }})</option>
                            @foreach($this->availablePeriods as $p)
                                @if(!$p->is_active)
                                    <option value="{{ $p->id }}" class="text-gray-900">{{ $p->name }} (Riwayat)</option>
                                @endif
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-indigo-300 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            <a href="/mahasiswa/classes"
               class="bg-white text-indigo-700 font-bold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg hover:scale-105 transition-all text-sm flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4"></i> Lihat Semua Kelas
            </a>
        </div>
    </div>
    
    {{-- Overview Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 mb-4">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="w-6 h-6 md:w-12 md:h-12 bg-indigo-50 border border-indigo-100/50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-600 md:w-6 md:h-6"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium tracking-wide">Kelas Diikuti</p>
                <p class="text-2xl font-black text-gray-900">{{ $stats['kelas'] }}</p>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="w-6 h-6 md:w-12 md:h-12 bg-emerald-50 border border-emerald-100/50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600 md:w-6 md:h-6"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium tracking-wide">Total Pertemuan</p>
                <p class="text-2xl font-black text-gray-900">{{ $stats['pertemuan'] }}</p>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4 col-span-2 md:col-span-1">
            <div class="w-6 h-6 md:w-12 md:h-12 bg-amber-50 border border-amber-100/50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-4 h-4 text-amber-600 md:w-6 md:h-6"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium tracking-wide">Kelompok</p>
                <p class="text-2xl font-black text-gray-900">{{ $stats['kelompok'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Recent Classes + My Groups --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Recent Classes --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-4 h-4 text-indigo-600"></i> Kelas Terbaru
                    </h2>
                    <a href="/mahasiswa/classes" class="text-xs text-indigo-600 font-semibold hover:underline">Lihat semua →</a>
                </div>
                @if(count($myClasses) === 0)
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Belum ada kelas. Gabung kelas dulu!</p>
                    <a href="/mahasiswa/classes" class="mt-3 inline-block text-indigo-600 text-sm font-semibold hover:underline">Gabung Kelas →</a>
                </div>
                @else
                <ul class="divide-y divide-gray-50">
                    @foreach($myClasses as $cls)
                    <li class="hover:bg-gray-50 transition">
                        <a href="/mahasiswa/classes/{{ $cls['id'] }}" class="flex items-center gap-4 px-6 py-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-indigo-600 font-black text-sm flex-shrink-0">
                                {{ strtoupper(substr($cls['course_name'], 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ $cls['course_name'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $cls['name'] }} · {{ $cls['lecturer'] }}</p>
                            </div>
                            <span class="text-xs bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-full font-semibold flex-shrink-0 border border-indigo-100/50">
                                {{ $cls['meetings'] }} Pertemuan
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            {{-- My Kelompok --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="users-round" class="w-4 h-4 text-amber-600"></i> Kelompok Saya
                    </h2>
                </div>
                @if(count($myKelompok) === 0)
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Belum tergabung dalam kelompok manapun.</p>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4">
                    @foreach($myKelompok as $k)
                    <div class="border border-gray-100 rounded-xl p-4 bg-gray-50 hover:bg-white hover:shadow-sm transition">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full bg-amber-50 border border-amber-100/50 text-amber-600 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($k['nama'], 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-900">{{ $k['nama'] }}</p>
                                @if($k['peran'] === 'ketua')
                                <span class="text-[10px] bg-indigo-50 border border-indigo-100/50 text-indigo-600 px-2 py-0.5 rounded-full font-bold">Ketua</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-2">{{ $k['mata_kuliah'] }} · {{ $k['kelas'] }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Pengumuman --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i data-lucide="bell" class="w-4 h-4 text-emerald-600"></i>
                <h2 class="font-bold text-gray-800">Pengumuman</h2>
            </div>
            @if(count($pengumuman) === 0)
            <div class="p-8 text-center text-gray-400 flex-1 flex flex-col items-center justify-center">
                <i data-lucide="bell-off" class="w-10 h-10 mb-2 opacity-40"></i>
                <p class="text-sm">Belum ada pengumuman.</p>
            </div>
            @else
            <div class="divide-y divide-gray-50 flex-1 overflow-y-auto max-h-[600px]">
                @foreach($pengumuman as $p)
                <div class="p-5 hover:bg-gray-50 transition">
                    <p class="font-bold text-sm text-gray-900 mb-1 leading-snug">{{ $p['judul'] }}</p>
                    <p class="text-xs text-gray-500 leading-relaxed font-medium line-clamp-3">{{ $p['konten'] }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100/50">{{ $p['dosen'] }}</span>
                        <span class="text-[10px] font-medium text-gray-400 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> {{ $p['waktu'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>
