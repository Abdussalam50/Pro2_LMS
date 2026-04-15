<div>
    <x-slot name="title">Manajemen Data Akademik - Pro2Lms</x-slot>

    {{-- Breadcrumbs & Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
            <a href="/admin/dashboard" class="hover:text-indigo-600 transition">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-indigo-600">Manajemen Data Akdemik</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-4xl font-black text-gray-900 tracking-tight">Manajemen Data</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola Mata Kuliah, Kelas, dan hubungkan dengan Periode Akademik.</p>
            </div>
            <div class="flex items-center gap-3">
                @if($currentTab === 'courses')
                    <button wire:click="openCourseModal" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:scale-105 transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Matkul
                    </button>
                @else
                    <button wire:click="openClassModal" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:scale-105 transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kelas
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs & Filters --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- Tab Switcher --}}
            <div class="flex bg-gray-100/80 p-1.5 rounded-2xl gap-1">
                <button wire:click="setTab('courses')" class="px-6 py-2.5 rounded-xl text-xs font-black transition-all uppercase tracking-widest {{ $currentTab === 'courses' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    Mata Kuliah
                </button>
                <button wire:click="setTab('classes')" class="px-6 py-2.5 rounded-xl text-xs font-black transition-all uppercase tracking-widest {{ $currentTab === 'classes' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    Kelas
                </button>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-4">
                @if($currentTab === 'classes')
                    <div class="relative min-w-[200px]">
                        <select wire:model.live="selectedPeriod" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none pr-10">
                            <option value="all">Semua Periode</option>
                            @foreach($periods as $p)
                                <option value="{{ $p->academic_period_id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="filter" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                @endif

                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Cari data..." class="bg-gray-50 border-gray-100 rounded-xl px-10 py-2.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all min-w-[250px]">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Table --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        @if($currentTab === 'courses')
            {{-- Course Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Kode & Nama</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Dosen Pengampu</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Jumlah Kelas</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($courses as $course)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-black text-sm">
                                            {{ strtoupper(substr($course->mata_kuliah, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900">{{ $course->mata_kuliah }}</p>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $course->kode }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                            {{ strtoupper(substr($course->dosen->nama ?? '?', 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-700">{{ $course->dosen->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-black">{{ $course->kelas->count() }} Kelas</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="openCourseModal('{{ $course->mata_kuliah_id }}')" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirm('Yakin ingin menghapus Matkul ini?') || event.stopImmediatePropagation()" wire:click="deleteCourse('{{ $course->mata_kuliah_id }}')" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic">Belum ada data mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-gray-50/50">
                {{ $courses->links() }}
            </div>
        @else
            {{-- Class Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Kode & Nama Kelas</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Mata Kuliah</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Periode Akademik</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($classes as $kelas)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-black text-sm text-center p-1 leading-tight">
                                            {{ $kelas->kelas }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900">{{ $kelas->kelas }}</p>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $kelas->kode }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-bold text-gray-700">{{ $kelas->mataKuliah->mata_kuliah ?? '-' }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    @if($kelas->academicPeriod)
                                        <span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border border-amber-100 whitespace-nowrap">
                                            {{ $kelas->academicPeriod->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs italic font-medium">Bukan Semester Aktif</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="openClassModal('{{ $kelas->kelas_id }}')" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="confirm('Yakin ingin menghapus kelas ini?') || event.stopImmediatePropagation()" wire:click="deleteClass('{{ $kelas->kelas_id }}')" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-gray-50/50">
                {{ $classes->links() }}
            </div>
        @endif
    </div>

    {{-- Course Modal --}}
    @if($showCourseModal)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg border border-gray-100 overflow-hidden transform animate-in zoom-in-95 duration-300">
                <div class="px-10 pt-10 pb-6">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i data-lucide="book-copy" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900">{{ $editingCourseId ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Informasi dasar mata kuliah</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveCourse" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Mata Kuliah</label>
                            <input type="text" wire:model="courseName" placeholder="Contoh: Pemrograman Web" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            @error('courseName') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kode Mata Kuliah</label>
                            <input type="text" wire:model="courseCode" placeholder="Contoh: MK001" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            @error('courseCode') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Dosen Pengampu</label>
                            <select wire:model="courseDosenId" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 appearance-none">
                                <option value="">Pilih Dosen...</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->dosen_id }}">{{ $d->nama }}</option>
                                @endforeach
                            </select>
                            @error('courseDosenId') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4 pb-4">
                            <button type="button" wire:click="$set('showCourseModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Class Modal --}}
    @if($showClassModal)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-[100] animate-in fade-in duration-300">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg border border-gray-100 overflow-hidden transform animate-in zoom-in-95 duration-300">
                <div class="px-10 pt-10 pb-6">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i data-lucide="school" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900">{{ $editingClassId ? 'Edit Kelas' : 'Tambah Kelas Baru' }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Konfigurasi kelas dan semester</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveClass" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Nama Kelas</label>
                            <input type="text" wire:model="className" placeholder="Contoh: TI-A" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                            @error('className') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Kode Kelas</label>
                            <input type="text" wire:model="classCode" placeholder="Contoh: TIA-01" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                            @error('classCode') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Mata Kuliah</label>
                            <select wire:model="classMataKuliahId" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-emerald-500/10 appearance-none">
                                <option value="">Pilih Matkul...</option>
                                @foreach($coursesList as $c)
                                    <option value="{{ $c->mata_kuliah_id }}">{{ $c->mata_kuliah }}</option>
                                @endforeach
                            </select>
                            @error('classMataKuliahId') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Periode Akademik (Opsional)</label>
                            <select wire:model="classPeriodId" class="w-full bg-gray-50 border-gray-100 rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-emerald-500/10 appearance-none">
                                <option value="">Pilih Periode...</option>
                                @foreach($periods as $p)
                                    <option value="{{ $p->academic_period_id }}">{{ $p->name }} {{ $p->is_active ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                            <p class="text-[9px] text-indigo-500 font-bold mt-2 ml-1">* Kosongkan jika ingin didaftarkan ke semester berjalan secara otomatis.</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 pb-4">
                            <button type="button" wire:click="$set('showClassModal', false)" class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Batal</button>
                            <button type="submit" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
