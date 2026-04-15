<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LMS Pro</title>
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/x-icon" href="{{ asset('icons/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/logo192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/logo512x512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon512_rounded.png') }}">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then(reg => console.log('SW registered:', reg))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100" x-data="{ isLogin: true }">
    <div class="bg-white p-8 rounded-xl shadow-md w-full" :class="isLogin ? 'max-w-md' : 'max-w-2xl'">
        
        <!-- Login Form -->
        <div x-show="isLogin" x-transition>
            <div class="flex flex-col items-center justify-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center shadow-lg border border-indigo-100 overflow-hidden mb-4">
                    <img src="/icons/logo192x192.png" class="w-full h-full object-cover" alt="Logo">
                </div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                    
                    Login Pro2LMS
                </h2>
            </div>
            
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4 text-black">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required
                    />
                </div>
                <button
                    type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition"
                >
                    Masuk
                </button>

                <div class="text-center mt-4 text-sm text-gray-600">
                    Belum punya akun? 
                    <button type="button" @click="isLogin = false" class="text-indigo-600 font-semibold hover:underline">
                        Daftar Mahasiswa
                    </button>
                </div>
            </form>
        </div>

        <!-- Register Form -->
        <div x-show="!isLogin" x-transition>
            @livewire('auth.register-mahasiswa')
        </div>
    </div>

    @livewireScripts
</body>
</html>