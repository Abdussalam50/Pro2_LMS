@php $user = auth()->user(); @endphp

<div class="max-w-3xl mx-auto pb-32 px-4" x-data="{ answerChanged: false }">

    {{-- Back button --}}
    <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-500 hover:text-indigo-600 mb-6 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>

    @if(empty($assignment))
        <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm border">
            <i data-lucide="file-x" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <p>Tugas tidak ditemukan.</p>
        </div>
    @elseif($submitted || $sudahDikerjakan)
        {{-- Submission / Review Screen --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Tugas Telah Dikumpulkan</h2>
                    <p class="text-xs text-gray-500 font-medium lowercase">Jawaban kamu sudah diterima sistem.</p>
                </div>
            </div>
        </div>

        @foreach($pages as $pIndex => $page)
            <div class="space-y-6 mb-12">
                @if($page['narasi'])
                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 italic text-sm text-gray-600 prose max-w-none">
                        {!! $page['narasi'] !!}
                    </div>
                @endif

                @foreach($page['soals'] as $idx => $soal)
                    @php 
                        $fb = $feedback[$soal['id']] ?? null;
                        $ans = $answers[$soal['id']] ?? null;
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div class="flex gap-4 mb-4">
                                <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0">{{ $idx + 1 }}</span>
                                <div class="prose prose-sm max-w-none text-gray-800 font-medium">
                                    {!! $soal['teks'] !!}
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100 mb-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-2">Jawaban Kamu:</p>
                                <div class="text-sm text-indigo-900 prose max-w-none">
                                    @if($ans)
                                        {!! $ans !!}
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada jawaban.</span>
                                    @endif
                                </div>
                            </div>

                            @if($fb && (!is_null($fb['score']) || !is_null($fb['note'])))
                                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Feedback Dosen:</p>
                                        @if(!is_null($fb['score']))
                                            <div class="bg-emerald-600 text-white px-3 py-1 rounded-lg text-xs font-black">
                                                Nilai: {{ $fb['score'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-sm text-emerald-900 font-medium prose prose-sm max-w-none prose-emerald">{!! $fb['note'] ?: '<span class="text-gray-400 italic">Tidak ada catatan.</span>' !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-md border-t border-gray-200 p-4 text-center">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Kelas
            </a>
        </div>
    @else
        {{-- Assignment Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            @if($this->isReadonly)
                <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 p-4 rounded-xl text-amber-800 shadow-sm animate-pulse">
                    <div class="bg-amber-100 p-2 rounded-lg">
                        <i data-lucide="archive" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm uppercase tracking-wider">Arsip / Read-Only</h4>
                        <p class="text-xs opacity-80 font-medium">Tugas ini berasal dari periode akademik yang sudah tidak aktif. Anda tidak dapat mengirimkan jawaban baru.</p>
                    </div>
                </div>
            @endif
            <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">{{ $assignment['title'] }}</h1>
                    @if($assignment['tenggat'])
                        <p class="text-sm text-red-500 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i> Tenggat: {{ $assignment['tenggat'] }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 flex-shrink-0">
                    @if($sudahDikerjakan)
                        <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-xs flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Sudah Dikerjakan
                        </span>
                    @endif
                    @if(count($pages) > 1)
                        <span class="bg-gray-100 text-gray-600 font-semibold px-3 py-1 rounded-full text-xs">
                            Halaman {{ $currentPage + 1 }} / {{ count($pages) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if(count($pages) === 0)
        <div class="bg-white rounded-2xl p-10 text-center text-gray-400 border">
            <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 opacity-40"></i><p>Belum ada soal di tugas ini.</p>
        </div>
        @else
        @php $page = $pages[$currentPage]; @endphp

        {{-- Narasi / Context --}}
        @if(!empty($page['narasi']))
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-6">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Kasus / Narasi</p>
            <div class="prose prose-sm text-indigo-900 max-w-none">{!! $page['narasi'] !!}</div>
        </div>
        @endif

        {{-- Questions --}}
        <div class="space-y-5 mb-6">
            @foreach($page['soals'] as $idx => $soal)
            <div wire:key="soal-{{ $soal['id'] }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-init="console.log('[Tugas] Soal {{ $idx + 1 }} - ID: {{ $soal['id'] }} - Tipe: {{ $soal['tipe'] }} - HasKunci: {{ $soal['has_kunci'] ? 'YES' : 'NO' }} - TipeKunci: {{ $soal['tipe_kunci'] ?? 'NULL' }}')">
                <div class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-sm">
                        {{ $idx + 1 }}
                    </span>
                    <div class="flex-1">
                        <div class="prose prose-sm text-gray-800 max-w-none mb-4">{!! $soal['teks'] !!}</div>
                        <p class="text-xs text-gray-400 mb-3">Bobot: {{ $soal['bobot'] ?? 10 }}</p>

                        @if($soal['tipe'] === 'esai')
                        <div x-data="{ showEditor: {{ empty($answers[$soal['id']] ?? '') ? 'false' : 'true' }} }">
                            <template x-if="!showEditor">
                                <button type="button" @click="showEditor = true" class="w-full flex justify-center items-center gap-2 py-4 border-2 border-dashed border-indigo-300 text-indigo-600 rounded-xl hover:bg-indigo-50 font-medium transition group">
                                    <i data-lucide="edit-3" class="w-5 h-5 group-hover:scale-110 transition"></i>
                                    Mulai Menjawab Essay
                                </button>
                            </template>
                            <div x-show="showEditor" 
                                 x-data="tinyEditor('answers.{{ $soal['id'] }}')" 
                                 style="display: none;" 
                                 class="mt-2 relative" 
                                 wire:ignore>
                                <textarea
                                    x-ref="textarea"
                                    class="w-full bg-gray-50/50 border-gray-100 rounded-xl p-4 min-h-[200px]"
                                    placeholder="Tuliskan jawaban esai Anda di sini..."
                                ></textarea>
                            </div>
                        </div>
                        @else
                        <div class="space-y-2">
                            @foreach($soal['options'] as $opt)
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                                {{ ($answers[$soal['id']] ?? '') === $opt['teks']
                                    ? 'bg-indigo-50 border-indigo-400 text-indigo-800'
                                    : 'border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                <input type="radio"
                                    name="soal-{{ $soal['id'] }}"
                                    value="{{ $opt['teks'] }}"
                                    {{ ($answers[$soal['id']] ?? '') === $opt['teks'] ? 'checked' : '' }}
                                    wire:click="setAnswer('{{ $soal['id'] }}', '{{ $opt['teks'] }}')"
                                    class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                <span class="text-sm font-medium prose prose-sm max-w-none [&>p]:m-0">{!! $opt['teks'] !!}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Fixed Bottom NavBar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] px-4 py-4 z-50">
            <div class="max-w-4xl mx-auto flex justify-between items-center relative">
                <button wire:click="prevPage" {{ $currentPage === 0 ? 'disabled' : '' }}
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition flex-1 sm:flex-none justify-center
                        {{ $currentPage === 0 ? 'text-gray-400 bg-gray-50 cursor-not-allowed border border-gray-100' : 'text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 shadow-sm' }}">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i> <span class="hidden sm:inline">Sebelumnya</span>
                </button>

                <div class="flex-1 flex justify-center text-xs font-semibold text-green-600 gap-1.5 opacity-0 transition-opacity duration-300"
                     x-data="{ show: false }"
                     x-init="
                        Livewire.on('saved', () => {
                            show = true;
                            setTimeout(() => show = false, 2000);
                        });
                     "
                     :class="{ 'opacity-100': show }">
                    <i data-lucide="cloud-lightning" class="w-4 h-4"></i> Tersimpan
                </div>

                @if($currentPage === count($pages) - 1)
                    @if(!$this->isReadonly)
                        <button wire:click="submit" wire:loading.attr="disabled"
                            style="background-color: #16a34a; color: white;"
                            class="flex items-center justify-center gap-2 hover:bg-green-700 px-8 py-2.5 rounded-xl font-bold shadow-md transition disabled:opacity-50 min-w-[200px] flex-1 sm:flex-none">
                            <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                                <i data-lucide="save" class="w-5 h-5"></i> Kirim
                            </span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Menyimpan...
                            </span>
                        </button>
                    @else
                        <button disabled class="flex items-center justify-center gap-2 bg-gray-300 text-gray-500 px-8 py-2.5 rounded-xl font-bold cursor-not-allowed min-w-[200px] flex-1 sm:flex-none">
                            <i data-lucide="lock" class="w-5 h-5"></i> Arsip Terkunci
                        </button>
                    @endif
                @else
                <button wire:click="nextPage"
                    style="background-color: #4f46e5; color: white;"
                    class="flex items-center justify-center gap-2 hover:bg-indigo-700 px-8 py-2.5 rounded-xl font-bold shadow-md transition min-w-[160px] flex-1 sm:flex-none">
                    <span class="hidden sm:inline">Selanjutnya</span> <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
                @endif
            </div>
        </div>
    @endif
</div>
