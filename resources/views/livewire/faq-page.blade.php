<div class="py-6">
    <x-slot name="title">FAQ & Bantuan - Pro2Lms</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 animate-in fade-in slide-in-from-top-4 duration-500">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-4">Ada yang Bisa Kami Bantu?</h1>
            <p class="text-lg text-gray-500 font-medium max-w-2xl mx-auto italic">Temukan jawaban untuk pertanyaan umum atau berikan masukan Anda kepada kami.</p>
            
            <div class="mt-8 max-w-xl mx-auto relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within:translate-x-1 duration-300">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pertanyaan Anda di sini..." class="block w-full pl-12 pr-4 py-4 bg-white border-2 border-gray-100 rounded-2xl shadow-xl shadow-indigo-50/50 focus:border-indigo-500 focus:ring-0 transition-all font-bold text-gray-800 placeholder:text-gray-300">
            </div>
        </div>

        <!-- FAQ Section with Accordion -->
        <div class="space-y-12 mb-20 animate-in fade-in duration-700 delay-200">
            @forelse($faqGroups as $kategori => $faqs)
                <div>
                    <h2 class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-6 border-b border-indigo-50 pb-2">{{ $kategori ?: 'UMUM' }}</h2>
                    <div class="space-y-4" x-data="{ activeFaq: null }">
                        @foreach($faqs as $faq)
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
                                <button @click="activeFaq = activeFaq === {{ $faq->id }} ? null : {{ $faq->id }}" class="w-full px-6 py-5 text-left flex justify-between items-center group">
                                    <span class="font-black text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $faq->pertanyaan }}</span>
                                    <i data-lucide="chevron-down" class="h-5 w-5 text-gray-400 transition-transform duration-300" :class="{ 'rotate-180 text-indigo-600': activeFaq === {{ $faq->id }} }"></i>
                                </button>
                                <div x-show="activeFaq === {{ $faq->id }}" x-collapse x-cloak>
                                    <div class="px-6 pb-6 text-gray-600 leading-relaxed font-medium">
                                        <hr class="border-gray-50 mb-4">
                                        {!! nl2br(e($faq->jawaban)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-100 animate-pulse">
                    <i data-lucide="search-x" class="h-16 w-16 mx-auto text-gray-200 mb-4"></i>
                    <p class="text-gray-400 font-bold">Tidak ada hasil ditemukan.</p>
                </div>
            @endforelse
        </div>

        <!-- Feedback Section -->
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100 border border-indigo-50 overflow-hidden mb-12 animate-in slide-in-from-bottom-8 duration-700 delay-300">
            <div class="bg-indigo-600 p-10 text-center relative overflow-hidden">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <h2 class="text-3xl font-black text-white tracking-tight mb-2">Nilai Pengalaman Anda</h2>
                    <p class="text-indigo-100 font-bold opacity-80 italic">Bagikan pendapat Anda untuk membantu kami meningkatkan Pro2LMS.</p>
                </div>
            </div>
            
            <div class="p-10">
                @if($hasSubmittedToday)
                    <div class="py-12 text-center">
                        <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 text-emerald-500 animate-bounce">
                            <i data-lucide="check-circle" class="h-10 w-10"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 mb-2">Terima Kasih Atas Masukannya!</h3>
                        <p class="text-gray-500 font-medium">Anda telah memberikan feedback hari ini. Kami akan memeriksanya segera.</p>
                    </div>
                @else
                    <form wire:submit.prevent="submitFeedback" class="space-y-10">
                        <!-- Star Rating -->
                        <div class="flex flex-col items-center">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Rating Keseluruhan</label>
                            <div class="flex gap-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" wire:click="setRating({{ $i }})" class="group">
                                        <i data-lucide="star" class="h-10 w-10 transition-all duration-300 {{ $rating >= $i ? 'fill-amber-400 text-amber-400 scale-125' : 'text-gray-200 group-hover:text-amber-200' }}"></i>
                                    </button>
                                @endfor
                            </div>
                            @error('rating') <span class="text-xs text-rose-500 font-bold mt-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Apa yang Anda Nilai?</label>
                                <select wire:model="kategoriFeedback" class="block w-full rounded-2xl border-2 border-gray-50 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-0 sm:text-sm font-bold h-14 px-4 transition-all">
                                    <option value="umum">Pengalaman Umum</option>
                                    <option value="tampilan">Tampilan & Navigasi</option>
                                    <option value="fitur">Fitur Pembelajaran</option>
                                    <option value="performa">Kecepatan & Performa</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Komentar Singkat (Opsional)</label>
                                <textarea wire:model="komentar" rows="4" class="block w-full rounded-2xl border-2 border-gray-50 bg-gray-50/50 shadow-sm focus:border-indigo-500 focus:ring-0 sm:text-sm font-medium p-4 transition-all h-32" placeholder="Tuliskan saran, kritik, atau apresiasi Anda di sini..."></textarea>
                                @error('komentar') <span class="text-xs text-rose-500 font-bold mt-2">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-center pt-4">
                            <button type="submit" class="inline-flex items-center px-10 py-5 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:scale-[1.02] active:scale-95 transition-all">
                                Kirim Masukan <i data-lucide="send" class="ml-3 h-5 w-5"></i>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Support Ticket Link Card -->
        <div class="bg-gradient-to-r from-gray-900 to-indigo-900 rounded-[2rem] p-10 text-white flex flex-col md:flex-row items-center justify-between gap-8 animate-in zoom-in duration-500 mb-20">
            <div class="text-center md:text-left">
                <h3 class="text-2xl font-black mb-1">Butuh Bantuan Lebih Lanjut?</h3>
                <p class="text-indigo-200 font-medium">Buat tiket untuk mendapatkan bantuan personal dari Admin.</p>
            </div>
            <a href="{{ url('/support/tickets') }}" class="px-8 py-4 bg-white text-indigo-900 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-indigo-50 transition-colors shadow-lg active:scale-95 group">
                Buat Tiket Bantuan <i data-lucide="ticket" class="inline-block ml-2 h-4 w-4 group-hover:rotate-12 transition-transform"></i>
            </a>
        </div>
    </div>
</div>
