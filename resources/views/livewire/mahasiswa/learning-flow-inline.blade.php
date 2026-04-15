<div class="mt-3">

    @if(empty($tahapanList))
        <div class="text-center py-8 text-gray-400 text-sm italic">Belum ada tahapan sintaks untuk pertemuan ini.</div>
    @else

    {{-- Horizontal Step Tabs --}}
    <div class="flex items-start gap-2 overflow-x-auto pb-2 mb-4">
        @foreach($tahapanList as $tahap)
            @php
                $isActive    = $tahap['id'] === $activeTahapId;
                $isCompleted = ($tahap['status'] ?? '') === 'completed';
            @endphp
            <button
                wire:click="setActiveTahap('{{ $tahap['id'] }}')"
                class="shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all
                    @if($isActive) bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100
                    @elseif($isCompleted) bg-emerald-50 text-emerald-700 border-emerald-200
                    @else bg-white text-gray-500 border-gray-200 hover:border-indigo-300 hover:text-indigo-600
                    @endif"
            >
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0
                    @if($isActive) bg-white/30 text-white
                    @elseif($isCompleted) bg-emerald-200 text-emerald-800
                    @else bg-gray-100 text-gray-500
                    @endif">
                    @if($isCompleted)
                        <i wire:ignore data-lucide="check" class="w-3 h-3"></i>
                    @else
                        {{ $tahap['urutan'] }}
                    @endif
                </span>
                {{ $tahap['nama'] }}
            </button>
        @endforeach
    </div>

    {{-- Active Stage Content --}}
    @if($this->activeTahap)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Stage Header --}}
        <div class="px-5 py-4 border-b border-gray-50 bg-gradient-to-r from-indigo-50 to-white flex items-center justify-between">
            <div>
                <div class="text-[9px] font-black uppercase tracking-widest text-indigo-400 mb-0.5">
                    Fase {{ $this->activeTahap['urutan'] }} Aktif
                </div>
                <h2 class="text-base font-black text-gray-900">{{ $this->activeTahap['nama'] }}</h2>
            </div>
            <span class="@if(($this->activeTahap['status'] ?? '') === 'completed') bg-emerald-100 text-emerald-700 @else bg-indigo-50 text-indigo-600 @endif text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-widest border border-current/20">
                @if(($this->activeTahap['status'] ?? '') === 'completed') Selesai @else Berlangsung @endif
            </span>
        </div>

        {{-- Content Tabs --}}
        <div class="flex items-center gap-1 px-5 py-3 border-b border-gray-50 bg-gray-50/50 overflow-x-auto">
            <button wire:click="setTab('materi')" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'materi' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                <i wire:ignore data-lucide="book-open" class="w-3 h-3 inline mr-1"></i> Materi
            </button>
            <button wire:click="setTab('tugas')" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'tugas' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                <i wire:ignore data-lucide="paperclip" class="w-3 h-3 inline mr-1"></i> Tugas
            </button>
            <button wire:click="setTab('diskusi')" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'diskusi' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                <i wire:ignore data-lucide="message-circle" class="w-3 h-3 inline mr-1"></i> Diskusi
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="p-5 min-h-[160px]">
            @if($activeTab === 'materi')
                @php 
                    $mat = $this->activeTahapMateri; 
                    $kegiatan = $this->activeTahap['kegiatan'] ?? [];
                @endphp

                @if(!empty($kegiatan))
                    <div class="mb-6">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Aktivitas</h4>
                        <div class="space-y-2">
                            @foreach($kegiatan as $keg)
                                <div class="flex items-center gap-2 p-2.5 bg-gray-50/50 border border-gray-100 rounded-xl">
                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></div>
                                    <span class="text-xs font-medium text-gray-600 leading-relaxed">{{ $keg }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($mat)
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Materi</h4>
                    <h4 class="text-sm font-bold text-gray-900 mb-2">{{ $mat->judul }}</h4>
                    <div class="prose prose-sm max-w-none text-gray-600">{!! $mat->isi_materi !!}</div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                        <i wire:ignore data-lucide="book-open" class="w-8 h-8 mb-2 opacity-30"></i>
                        <p class="text-sm">Belum ada materi untuk tahap ini.</p>
                    </div>
                @endif

            @elseif($activeTab === 'tugas')
                @php $tugas = $this->activeTahapTugas; @endphp
                @if($tugas)
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <h4 class="font-black text-gray-900 mb-1 flex items-center gap-2">
                            <i wire:ignore data-lucide="clipboard-list" class="w-4 h-4 text-amber-500"></i>
                            {{ $tugas->master_soal }}
                        </h4>
                        @if($tugas->tenggat_waktu)
                            <p class="text-xs text-rose-500 font-semibold mt-1">
                                <i wire:ignore data-lucide="clock" class="w-3 h-3 inline"></i>
                                Tenggat: {{ \Carbon\Carbon::parse($tugas->tenggat_waktu)->translatedFormat('d M Y, H:i') }}
                            </p>
                        @endif
                        <a href="/mahasiswa/soal/{{ $tugas->master_soal_id }}"
                           class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-black uppercase tracking-widest shadow-md shadow-indigo-100 hover:bg-indigo-700 transition-all">
                            <i wire:ignore data-lucide="arrow-right" class="w-3.5 h-3.5"></i> Kerjakan Tugas
                        </a>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                        <i wire:ignore data-lucide="clipboard-list" class="w-8 h-8 mb-2 opacity-30"></i>
                        <p class="text-sm">Belum ada tugas untuk tahap ini.</p>
                    </div>
                @endif

            @elseif($activeTab === 'diskusi')
                <div class="h-[450px]">
                    @livewire('discussion-hub', [
                        'kelasId' => $kelasId, 
                        'pertemuanId' => $pertemuanId, 
                        'tahapanSintaksId' => $activeTahapId,
                        'scope' => 'class',
                        'compact' => true
                    ], key('hub-'.$activeTahapId))
                </div>
            @endif
        </div>

        @if(session()->has('error_message'))
            <div class="px-5 py-2 bg-rose-50 border-t border-rose-100 text-rose-600 text-[10px] font-bold uppercase tracking-wider text-center">
                {{ session('error_message') }}
            </div>
        @endif

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-gray-50 flex justify-end bg-gray-50/30">
            <button wire:click="markAsCompleted"
                class="flex items-center gap-2 px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all
                @if(($this->activeTahap['status'] ?? '') === 'completed')
                    bg-emerald-100 text-emerald-700 border border-emerald-200
                @else
                    bg-indigo-600 text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95
                @endif">
                @if(($this->activeTahap['status'] ?? '') === 'completed')
                    <i wire:ignore data-lucide="check" class="w-3.5 h-3.5"></i> Tahap Selesai
                @else
                    <i wire:ignore data-lucide="check-circle" class="w-3.5 h-3.5"></i> Tandai Selesai
                @endif
            </button>
        </div>
    </div>
    @endif

    @endif
</div>
