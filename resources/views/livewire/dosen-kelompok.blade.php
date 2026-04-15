<div class="max-w-[1650px] mx-auto min-h-screen">
    <!-- Sleek Header Section -->
    <div class="mb-8 relative overflow-hidden bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-8">
        <!-- Decorative bg blur -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-purple-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6 z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-bold tracking-widest uppercase mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Workflow Dosen
                </div>
                <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">Manajemen Kelompok</h1>
                <p class="text-slate-500 mt-2 text-sm lg:text-base max-w-xl leading-relaxed">Kelola distribusi mahasiswa ke dalam tim-tim kecil. Cukup <span class="font-semibold text-slate-700 block inline-block bg-slate-100 px-1.5 py-0.5 rounded text-xs mx-0.5">tarik & lepas (drag & drop)</span> untuk memindahkan anggota antar kelompok.</p>
            </div>
            
            <!-- Class Selection Dropdown -->
            <div class="w-full md:w-80 shrink-0">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><i data-lucide="book-open" class="w-3.5 h-3.5"></i> Mata Kuliah / Kelas</label>
                <div class="relative group">
                    <select wire:model.live="selectedKelasId" class="w-full appearance-none bg-slate-50 border-0 ring-1 ring-inset ring-slate-200 text-slate-700 py-3.5 px-5 pr-12 rounded-2xl focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 font-semibold transition-all duration-300 cursor-pointer shadow-sm group-hover:bg-white group-hover:shadow-md">
                        @if(empty($kelasList))
                            <option value="">-- Belum ada kelas terdaftar --</option>
                        @endif
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas['id'] }}">{{ $kelas['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 group-hover:text-indigo-500 transition-colors">
                        <i data-lucide="chevrons-up-down" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($selectedKelasId))
        <!-- Main Workspace (2 columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            
            <!-- LEFT COLUMN: Unassigned Students (Sidebar style) -->
            <div class="lg:col-span-3 lg:col-start-1 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col h-[750px] overflow-hidden">
                <div class="px-6 py-5 bg-[#1e1b4b] border-b border-[#2d2966] text-white relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-500/0 pointer-events-none"></div>
                    <div class="relative z-10">
                        <h3 class="font-bold flex items-center gap-3 text-lg tracking-tight">
                            <div class="p-1.5 bg-white/10 rounded-lg backdrop-blur-md">
                                <i data-lucide="users" class="w-5 h-5 text-indigo-200"></i>
                            </div>
                            Belum Masuk Tim
                        </h3>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs text-indigo-200 font-medium tracking-wide">Tersedia untuk ditarik:</span>
                            <span class="bg-indigo-500/30 text-indigo-100 border border-indigo-400/30 text-xs px-2.5 py-1 rounded-full font-bold shadow-inner">{{ count($unassignedStudents) }} Org</span>
                        </div>
                    </div>
                </div>
                
                <div 
                    class="p-4 flex-1 overflow-y-auto bg-slate-50/50 space-y-3 sortable-list min-h-[150px]" 
                    data-group-id="unassigned"
                >
                    @if(count($unassignedStudents) === 0)
                        <div class="flex flex-col items-center justify-center h-full opacity-60 text-center px-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 inner-shadow">
                                <i data-lucide="check" class="w-8 h-8 text-green-500"></i>
                            </div>
                            <h4 class="font-bold text-slate-700">Kosong</h4>
                            <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">Semua mahasiswa di kelas ini telah didistribusikan ke kelompok.</p>
                        </div>
                    @else
                        @foreach($unassignedStudents as $student)
                            <!-- Draggable Student Card -->
                            <div 
                                class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-[0_8px_25px_rgb(0,0,0,0.06)] flex items-center gap-3.5 cursor-grab active:cursor-grabbing hover:border-indigo-200 transition-all duration-300 group relative transform hover:-translate-y-0.5"
                                data-user-id="{{ $student['id'] }}"
                            >
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-700 flex items-center justify-center font-extrabold text-sm flex-shrink-0 border border-slate-100 shadow-inner group-hover:from-indigo-100 group-hover:to-indigo-50 transition-colors">
                                    {{ substr($student['name'], 0, 1) }}
                                </div>
                                <div class="overflow-hidden flex-1">
                                    <div class="font-bold text-sm text-slate-800 truncate leading-snug">{{ $student['name'] }}</div>
                                    <div class="text-[11px] font-semibold text-slate-400 truncate mt-0.5 group-hover:text-slate-500 transition-colors">{{ $student['email'] }}</div>
                                </div>
                                <div class="w-6 h-6 flex items-center justify-center rounded-md bg-slate-50 text-slate-300 opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:scale-110">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- RIGHT COLUMN: Group Containers -->
            <div class="lg:col-span-9 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col h-[750px] overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white z-10 relative">
                    <div>
                        <h3 class="font-extrabold text-slate-800 flex items-center gap-2.5 text-xl tracking-tight">
                            <div class="p-1.5 bg-purple-50 rounded-lg">
                                <i data-lucide="layout-grid" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            Wadah Kelompok
                        </h3>
                        <p class="text-xs font-medium text-slate-500 mt-2 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i> Tarik kotak mahasiswa dari kiri ke dalam area di bawah.
                        </p>
                    </div>
                    <button wire:click="$set('showCreateModal', true)" class="group bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-600 transition-all duration-300 flex items-center justify-center gap-2 shadow-[0_4px_14px_0_rgb(0,0,0,0.1)] hover:shadow-[0_6px_20px_rgba(79,70,229,0.23)] hover:-translate-y-0.5 active:translate-y-0 w-full sm:w-auto">
                        <i data-lucide="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300"></i> Buat Wadah Baru
                    </button>
                </div>
                
                <div class="p-6 flex-1 overflow-y-auto bg-slate-50/50 relative">
                    @if(count($kelompokList) === 0)
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center bg-slate-50/30">
                            <div class="relative w-32 h-32 mb-6">
                                <div class="absolute inset-0 bg-indigo-100 rounded-full blur-xl opacity-50 animate-pulse"></div>
                                <div class="relative w-full h-full bg-white rounded-3xl shadow-sm border border-slate-100 flex items-center justify-center rotate-3 hover:rotate-6 transition-transform">
                                    <i data-lucide="boxes" class="w-14 h-14 text-indigo-300"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-extrabold text-slate-700 tracking-tight">Belum Ada Kelompok</h4>
                            <p class="text-slate-500 max-w-sm mt-3 text-sm font-medium leading-relaxed">Buat wadah kelompok baru terlebih dahulu untuk mulai menyusun tim kelas ini.</p>
                            <button wire:click="$set('showCreateModal', true)" class="mt-8 bg-white border-2 border-slate-200 text-slate-700 px-6 py-3 rounded-xl font-bold hover:border-indigo-500 hover:text-indigo-600 transition-all shadow-sm">
                                Buat Kelompok Sekarang
                            </button>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6 auto-rows-max pb-8">
                            @foreach($kelompokList as $index => $group)
                                <!-- Modern Group Card Container -->
                                <div class="bg-white rounded-[1.5rem] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col h-[28rem] overflow-hidden group/card relative ring-1 ring-slate-900/5 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 transform hover:-translate-y-1">
                                    
                                    <!-- Dynamic Color Header Strip based on index -->
                                    <div class="h-1.5 w-full {{ ['bg-indigo-500', 'bg-purple-500', 'bg-blue-500', 'bg-teal-500', 'bg-rose-500', 'bg-amber-500'][$index % 6] }}"></div>
                                    
                                    <!-- Group Header -->
                                    <div class="px-6 py-5 border-b border-slate-100/80 flex flex-col gap-3 relative bg-gradient-to-b from-slate-50/50 to-white">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-extrabold text-slate-800 text-lg tracking-tight leading-tight pr-4">
                                                {{ $group['name'] }}
                                            </h4>
                                            
                                            <!-- Action Buttons (Subtle until hover) -->
                                            <div class="flex items-center gap-1 opacity-40 group-hover/card:opacity-100 transition-opacity duration-300 bg-slate-100 rounded-lg p-1 shadow-inner">
                                                <button wire:click="editKelompok('{{ $group['id'] }}', '{{ $group['name'] }}')" class="text-slate-500 hover:text-indigo-600 hover:bg-white hover:shadow p-1.5 rounded-md transition-all" title="Edit Nama">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button onclick="confirmDeleteKelompok('{{ $group['id'] }}', '{{ addslashes($group['name']) }}')" class="text-slate-500 hover:text-red-600 hover:bg-white hover:shadow p-1.5 rounded-md transition-all" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Capacity / Member Count Badge -->
                                        <div class="inline-flex max-w-max items-center gap-1.5 {{ count($group['members']) > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500' }} text-xs px-3 py-1.5 rounded-lg font-bold">
                                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                            {{ count($group['members']) }} Anggota
                                        </div>
                                    </div>

                                    <!-- Group Members Dropzone -->
                                    <div 
                                        class="flex-1 p-5 overflow-y-auto bg-slate-50/40 sortable-list space-y-3 min-h-[150px] scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent"
                                        data-group-id="{{ $group['id'] }}"
                                    >
                                        @if(count($group['members']) === 0)
                                            <!-- Empty Dropzone State -->
                                            <div class="h-full flex flex-col items-center justify-center border-2 border-dashed border-slate-200/80 rounded-2xl text-slate-400 text-sm font-bold text-center p-6 bg-slate-50/50 transition-colors group-hover/card:border-indigo-200 group-hover/card:bg-indigo-50/30">
                                                <div class="w-12 h-12 rounded-xl bg-white shadow-sm ring-1 ring-slate-100 flex items-center justify-center mb-3 text-slate-300 group-hover/card:text-indigo-300 transition-colors">
                                                    <i data-lucide="download" class="w-5 h-5"></i>
                                                </div>
                                                Jatuhkan ke sini
                                            </div>
                                        @else
                                            @foreach($group['members'] as $member)
                                                <!-- Inside-Group Member Card -->
                                                <div 
                                                    class="p-3.5 rounded-2xl border {{ $member['role'] === 'ketua' ? 'border-amber-200 bg-amber-50 shadow-[0_4px_12px_rgb(251,191,36,0.15)] ring-1 ring-amber-100/50' : 'bg-white border-slate-200/80 hover:border-indigo-300 hover:shadow-md' }} flex items-center gap-3.5 cursor-grab active:cursor-grabbing transition-all duration-300 relative group/item transform hover:-translate-y-0.5"
                                                    data-user-id="{{ $member['id'] }}"
                                                >
                                                    <!-- Avatar -->
                                                    <div class="w-10 h-10 rounded-full {{ $member['role'] === 'ketua' ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 border border-slate-200 shadow-inner' }} flex items-center justify-center font-extrabold text-sm flex-shrink-0 relative overflow-hidden">
                                                        {{ substr($member['name'], 0, 1) }}
                                                        @if($member['role'] === 'ketua')
                                                            <div class="absolute inset-0 bg-white/20"></div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Info -->
                                                    <div class="overflow-hidden flex-1">
                                                        <div class="font-bold text-sm {{ $member['role'] === 'ketua' ? 'text-amber-900' : 'text-slate-800' }} truncate leading-tight">{{ $member['name'] }}</div>
                                                        <div class="mt-1 flex items-center gap-1.5">
                                                            @if($member['role'] === 'ketua')
                                                                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded text-[10px] font-bold border border-amber-200/50">
                                                                    <i data-lucide="crown" class="w-3 h-3"></i> KETUA
                                                                </span>
                                                            @else
                                                                <span class="text-xs font-semibold text-slate-400">Anggota</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Floating Quick Actions (Hover) -->
                                                    <div class="absolute right-3 opacity-0 group-hover/item:opacity-100 transition-all duration-200 translate-x-2 group-hover/item:translate-x-0 flex gap-1 bg-white/95 backdrop-blur-md p-1.5 rounded-xl shadow-[0_4px_15px_rgb(0,0,0,0.08)] ring-1 ring-slate-900/5 z-10">
                                                        @if($member['role'] !== 'ketua')
                                                            <button wire:click="setAsKetua('{{ $group['id'] }}', '{{ $member['id'] }}')" class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Jadikan Ketua">
                                                                <i data-lucide="crown" class="w-4 h-4"></i>
                                                            </button>
                                                        @endif
                                                        <button wire:click="updateGroupAssignment('unassigned', '{{ $member['id'] }}')" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Keluarkan">
                                                            <i data-lucide="log-out" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <!-- Decorative bottom fade to indicate scrollable content if very full -->
                                    <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-white/80 to-transparent pointer-events-none rounded-b-[1.5rem]"></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Empty State When No Class is Selected -->
        <div class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-16 text-center text-slate-800 flex flex-col items-center justify-center min-h-[500px]">
            <div class="relative w-24 h-24 mb-6">
                <div class="absolute inset-0 bg-slate-100 rounded-full blur-xl opacity-60"></div>
                <div class="relative w-full h-full bg-slate-50 rounded-full border border-slate-200 flex items-center justify-center text-slate-300">
                    <i data-lucide="library" class="w-10 h-10"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold tracking-tight">Menunggu Pilihan Kelas...</h3>
            <p class="text-slate-500 mt-3 font-medium max-w-md mx-auto leading-relaxed">Silakan tentukan mata kuliah / kelas melalui menu *dropdown* di bagian atas untuk mulai mengelola kelompok mahasiswanya.</p>
        </div>
    @endif

    <!-- Sleek Modals -->
    <!-- Create / Edit Modals merged into similar stylized structures -->
    
    @if($showCreateModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
            <div class="bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] w-full max-w-md transform transition-all relative z-10 overflow-hidden ring-1 ring-white/50">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2"><i data-lucide="plus-square" class="w-5 h-5 text-indigo-500"></i> Buat Wadah Kelompok</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-700 bg-white hover:bg-slate-100 p-1.5 rounded-full transition-colors shadow-sm"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <div class="p-8">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Kelompok</label>
                        <input wire:model="newKelompokName" wire:keydown.enter="createKelompok" placeholder="Contoh: Kelompok Riset Alpha" autofocus class="block w-full bg-slate-50 border-0 ring-1 ring-inset ring-slate-200/80 text-slate-800 focus:ring-2 focus:ring-inset focus:ring-indigo-500 rounded-xl px-4 py-3.5 font-semibold text-sm transition-all shadow-inner placeholder-slate-300" />
                        @error('newKelompokName') <span class="text-xs font-bold text-red-500 mt-2 flex items-center gap-1"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showCreateModal', false)" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-xl font-bold text-sm transition-colors">Batal</button>
                    <button wire:click="createKelompok" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 font-bold text-sm transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">Buat Kelompok</button>
                </div>
            </div>
        </div>
    @endif

    @if($showEditModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] w-full max-w-md transform transition-all relative z-10 overflow-hidden ring-1 ring-white/50">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2"><i data-lucide="edit-3" class="w-5 h-5 text-indigo-500"></i> Rename Kelompok</h3>
                    <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-700 bg-white hover:bg-slate-100 p-1.5 rounded-full transition-colors shadow-sm"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <div class="p-8">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Kelompok Baru</label>
                        <input wire:model="editKelompokName" wire:keydown.enter="updateKelompok" placeholder="Ketik nama baru di sini..." autofocus class="block w-full bg-slate-50 border-0 ring-1 ring-inset ring-slate-200/80 text-slate-800 focus:ring-2 focus:ring-inset focus:ring-indigo-500 rounded-xl px-4 py-3.5 font-semibold text-sm transition-all shadow-inner placeholder-slate-300" />
                        @error('editKelompokName') <span class="text-xs font-bold text-red-500 mt-2 flex items-center gap-1"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showEditModal', false)" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-xl font-bold text-sm transition-colors">Batal</button>
                    <button wire:click="updateKelompok" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 font-bold text-sm transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript for Drag and Drop using SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    @script
    <script>
        const initSortable = () => {
            if (typeof window.Sortable === 'undefined') return;
            
            const containers = document.querySelectorAll('.sortable-list');
            
            containers.forEach(container => {
                if(container.sortableInstance) {
                    try { container.sortableInstance.destroy(); } catch(e) {}
                }

                container.sortableInstance = new window.Sortable(container, {
                    group: 'shared-students', 
                    animation: 200,
                    easing: "cubic-bezier(1, 0, 0, 1)",
                    ghostClass: 'opacity-40',
                    dragClass: 'shadow-2xl',
                    delay: 50,
                    delayOnTouchOnly: true,
                    onEnd: (evt) => {
                        const itemEl = evt.item;  
                        const toList = evt.to;    
                        
                        const userId = itemEl.getAttribute('data-user-id');
                        const newGroupId = toList.getAttribute('data-group-id');
                        const oldGroupId = evt.from.getAttribute('data-group-id');
                        
                        if (newGroupId !== oldGroupId) {
                            $wire.updateGroupAssignment(newGroupId, userId);
                        }
                    },
                });
            });
        };

        // Initialize directly if CDN is cached
        initSortable();

        // Retry loop in case CDN is slow initially
        let sortableCheck = setInterval(() => {
            if (typeof window.Sortable !== 'undefined') {
                clearInterval(sortableCheck);
                initSortable();
            }
        }, 200);

        // Re-initialize Sortable after THIS specific Livewire component DOM completes morphing
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                setTimeout(() => {
                    initSortable();
                }, 50);
            })
        });
    </script>
    @endscript
    
    <style>
        /* Custom scrollbar for inner lists to keep them looking clean */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
    </style>
    @push('scripts')
    <script>
        function confirmDeleteKelompok(id, title) {
            window.dispatchEvent(new CustomEvent('swal', {
                detail: {
                    icon: 'warning',
                    title: 'Hapus Kelompok?',
                    message: `Apakah Anda yakin ingin menghapus kelompok "${title}"? Anggota di dalamnya akan kembali ke daftar mahasiswa yang belum masuk tim.`,
                    confirm: true,
                    confirmText: 'Ya, Hapus',
                    cancel: true,
                    onConfirm: {
                        componentId: "{{ $this->getId() }}",
                        method: 'deleteKelompok',
                        params: [id]
                    }
                }
            }));
        }
    </script>
    @endpush
</div>
