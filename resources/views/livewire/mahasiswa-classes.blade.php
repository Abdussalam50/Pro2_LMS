<div>
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Kelas & Materi</h1>
            <p class="text-gray-500 mt-1 font-medium">Daftar kelas yang Anda ikuti.</p>
        </div>

        <div class="bg-white p-2 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2 lg:w-96">
            <input 
                type="text" 
                wire:model="joinCode" 
                placeholder="Masukkan Kode Kelas..." 
                class="flex-1 bg-transparent border-none focus:ring-0 text-sm px-3 text-black"
                @keydown.enter="$wire.joinClass()"
            />
            <button 
                wire:click="joinClass"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-indigo-700 active:scale-95 transition-all whitespace-nowrap"
            >
                Gabung Kelas
            </button>
        </div>
    </div>

    @if($errorMessage)
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ $errorMessage }}</span>
        </div>
    @endif

    @if($successMessage)
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            <span class="text-sm font-medium">{{ $successMessage }}</span>
        </div>
    @endif

    @if(count($myClasses) === 0)
        <div class="flex flex-col items-center justify-center min-h-[50vh] text-center p-8">
            <div class="bg-indigo-50 p-6 rounded-full mb-6">
                <i data-lucide="book-open" class="text-indigo-600 w-16 h-16"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Belum ada kelas</h2>
            <p class="text-gray-500 mb-6 max-w-md">Anda belum terdaftar di kelas manapun. Mintalah kode unik kelas kepada Dosen Anda lalu masukkan pada kolom di atas.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($myClasses as $class)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group flex flex-col h-full relative cursor-pointer" onclick="window.location.href='/mahasiswa/classes/{{ $class['id'] }}'">
                    <div class="h-24 relative overflow-hidden flex flex-col justify-end p-5 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
                        <h3 class="text-xl font-bold text-white relative z-10 line-clamp-1">{{ $class['course_name'] }}</h3>
                        <p class="text-indigo-100 text-sm font-medium opacity-90 relative z-10">{{ $class['name'] }} ({{ $class['course_code'] }})</p>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-gray-600 text-sm mb-4">
                                <i data-lucide="user" class="w-4 h-4"></i> Dosen: {{ $class['lecturer_name'] }}
                            </div>
                            <div class="flex items-center gap-2 text-gray-500 text-sm">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                {{ $class['meetings_count'] }} Pertemuan
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-5">
                            <span class="text-xs font-medium text-gray-400">{{ $class['meetings_count'] }} Pertemuan</span>
                            <div class="text-indigo-600 font-bold text-sm bg-indigo-50 px-3 py-1.5 rounded-lg flex items-center gap-1 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                Masuk Kelas <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
