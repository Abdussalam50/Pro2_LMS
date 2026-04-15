<div class="space-y-4">
    <div class="flex flex-col items-center justify-center mb-6">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center shadow-lg border border-indigo-100 overflow-hidden mb-4">
            <img src="/icons/logo192x192.png" class="w-full h-full object-cover" alt="Logo">
        </div>
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Registrasi Mahasiswa</h2>
    </div>


    <form wire:submit.prevent="register" class="space-y-4 text-black">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input
                    type="text"
                    wire:model="name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    type="email"
                    wire:model="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">NIM</label>
                <input
                    type="text"
                    wire:model="nim"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('nim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
                <input
                    type="text"
                    wire:model="no_wa"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('no_wa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Angkatan</label>
                <input
                    type="text"
                    wire:model="angkatan"
                    placeholder="Contoh: 2023"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('angkatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Program Studi</label>
                <input
                    type="text"
                    wire:model="program_studi"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('program_studi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input
                    type="password"
                    wire:model="password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input
                    type="password"
                    wire:model="password_confirmation"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    required
                />
            </div>
        </div>

        <div class="pt-4">
            <button
                type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-xl hover:bg-indigo-700 transition font-semibold shadow-lg shadow-indigo-200"
            >
                Daftar Sekarang
            </button>
        </div>
        
        <div class="text-center mt-4 text-sm text-gray-600">
            Sudah punya akun? 
            <button type="button" @click="isLogin = true" class="text-indigo-600 font-semibold hover:underline">
                Login di sini
            </button>
        </div>
    </form>
</div>
