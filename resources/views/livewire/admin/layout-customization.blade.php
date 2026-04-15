<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/40 shadow-xl shadow-indigo-900/5 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Kustomisasi Layout</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Personalisasi Tema & Ikon untuk Dosen dan Mahasiswa</p>
        </div>
        <button wire:click="save" class="flex items-center gap-3 px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 group">
            <i data-lucide="save" class="w-4 h-4"></i>
            Simpan Perubahan
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @foreach(['mahasiswa', 'dosen'] as $role)
            <div class="space-y-6 bg-white/40 backdrop-blur-xl p-10 rounded-[3rem] border border-white/40 shadow-2xl shadow-indigo-900/5">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i data-lucide="{{ $role === 'dosen' ? 'graduation-cap' : 'users-2' }}" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase">Tema {{ $role }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Atur visualisasi khusus untuk {{ $role }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Warna Utama (Primary)</label>
                            <div class="flex items-center gap-4">
                                <input type="color" wire:model.live="settings.{{ $role }}.primary_color" class="w-16 h-16 rounded-2xl border-none cursor-pointer p-1 bg-white shadow-inner" />
                                <input type="text" wire:model="settings.{{ $role }}.primary_color" class="flex-1 bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-xs font-black uppercase tracking-widest" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Sidebar Background</label>
                            <div class="flex items-center gap-4">
                                <input type="color" wire:model.live="settings.{{ $role }}.sidebar_bg" class="w-16 h-16 rounded-2xl border-none cursor-pointer p-1 bg-white shadow-inner" />
                                <input type="text" wire:model="settings.{{ $role }}.sidebar_bg" class="flex-1 bg-gray-50/50 border-gray-100 rounded-2xl p-4 text-xs font-black uppercase tracking-widest" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Ikon Aplikasi (Lucide Name)</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                    <i data-lucide="{{ $settings[$role]['app_icon'] }}" class="w-5 h-5 text-indigo-500"></i>
                                </div>
                                <input type="text" wire:model.live="settings.{{ $role }}.app_icon" class="w-full bg-gray-50/50 border-gray-100 rounded-2xl py-4 pl-12 pr-4 text-xs font-black uppercase tracking-widest" />
                            </div>
                        </div>
                    </div>

                    <!-- Mini Preview -->
                    <div class="bg-gray-100/30 rounded-[2.5rem] p-6 border border-gray-200/50 flex flex-col gap-4">
                        <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest text-center mb-2">Live Preview</div>
                        
                        <div class="w-full h-48 rounded-2xl shadow-2xl overflow-hidden flex ring-1 ring-black/5">
                            <!-- Sidebar Mini -->
                            <div class="w-16 h-full p-2 flex flex-col gap-2" style="background-color: {{ $settings[$role]['sidebar_bg'] }}">
                                <div class="w-6 h-6 rounded-md mb-2 opacity-50 shadow-sm" style="background-color: {{ $settings[$role]['primary_color'] }}"></div>
                                <div class="w-full h-1 bg-white/10 rounded-full"></div>
                                <div class="w-full h-1 bg-white/10 rounded-full"></div>
                                <div class="w-full h-1 bg-white/10 rounded-full"></div>
                                <div class="w-5 h-5 mx-auto mt-auto mb-2 rounded bg-indigo-500/20 border border-white/10"></div>
                            </div>
                            <!-- Content Mini -->
                            <div class="flex-1 bg-white p-4 space-y-3">
                                <div class="w-2/3 h-2 bg-gray-100 rounded-full"></div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="h-10 rounded-lg border-2 border-dashed border-gray-50" style="border-color: {{ $settings[$role]['primary_color'] }}20"></div>
                                    <div class="h-10 rounded-lg" style="background-color: {{ $settings[$role]['primary_color'] }}10"></div>
                                </div>
                                <div class="w-full h-12 rounded-xl shadow-lg shadow-indigo-500/10 flex items-center justify-center text-[8px] font-black text-white uppercase tracking-widest" style="background-color: {{ $settings[$role]['primary_color'] }}">
                                    Button
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
