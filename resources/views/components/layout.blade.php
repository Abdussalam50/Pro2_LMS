<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/x-icon" href="{{ asset('icons/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/logo192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/logo512x512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon512_rounded.png') }}">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Lucide Icons is now bundled via Vite -->
    
    
    <!-- KaTeX CSS & Auto-render extension for global math rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .ML__keyboard {
            z-index: 9999999999 !important;
        }
    </style>
    <!-- MathLive CSS for rendering equations properly outside TinyMCE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive@0.108.3/mathlive-static.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mathlive@0.108.3/mathlive-fonts.css" />
    
    @php
        $user = auth()->user();
        $role = $user ? $user->role : 'guest';
        $primary = \App\Models\SiteSetting::get($role . '_layout_primary_color', '#4f46e5');
        $sidebar_bg = \App\Models\SiteSetting::get($role . '_layout_sidebar_bg', '#1e1b4b');
        $sidebar_border = \App\Models\SiteSetting::get($role . '_layout_sidebar_border', '#312e81');
        $sidebar_accent = \App\Models\SiteSetting::get($role . '_layout_sidebar_accent', '#4f46e5');
        $app_icon = \App\Models\SiteSetting::get($role . '_layout_app_icon', 'book-open');
    @endphp
    <style>
        :root {
            --primary-color: {{ $primary }};
            --sidebar-bg: {{ $sidebar_bg }};
            --sidebar-border: {{ $sidebar_border }};
            --sidebar-accent: {{ $sidebar_accent }};
        }
    </style>

    <x-head.tinymce-config/>

    {{-- Firebase JS SDK --}}
    <script type="module">
        // ⚠️ Ganti nilai ini dengan Web App config dari Firebase Console → Project Settings → General → Your Apps
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
        import { getMessaging, getToken, onMessage } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js';

        const firebaseConfig = {
            apiKey:            "{{ config('firebase.api_key') }}",
            authDomain:        "{{ config('firebase.auth_domain') }}",
            projectId:         "{{ config('firebase.project_id') }}",
            storageBucket:     "{{ config('firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
            appId:             "{{ config('firebase.app_id') }}",
        };

        console.log('FCM Config Loaded.');

        // VAPID Key: Firebase Console → Project Settings → Cloud Messaging → Web Push Certificates
        const VAPID_KEY = "{{ config('firebase.vapid_key') }}";
        console.log('FCM VAPID Key:', VAPID_KEY ? 'Present (starts with ' + VAPID_KEY.substring(0, 5) + '...)' : 'MISSING!');

        @auth
        console.log('FCM: User is Authenticated. Initializing Firebase...');
        const app       = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Register service worker + request push permission
        console.log('FCM: Checking environment...', {
            https: window.location.protocol === 'https:',
            serviceWorker: 'serviceWorker' in navigator,
            notification: 'Notification' in window,
            permission: Notification.permission
        });

        if ('serviceWorker' in navigator && Notification.permission !== 'denied') {
            console.log('FCM: Attempting to register service worker...');
            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(async (reg) => {
                    try {
                        console.log('FCM: SW registered, checking status...');
                        
                        // Tunggu sampai service worker benar-benar aktif
                        if (!reg.active) {
                            console.log('FCM: SW is not active yet (state: ' + (reg.installing ? 'installing' : 'waiting') + '), waiting...');
                            await new Promise((resolve) => {
                                const sw = reg.installing || reg.waiting;
                                if (sw) {
                                    sw.addEventListener('statechange', (e) => {
                                        if (e.target.state === 'active') {
                                            console.log('FCM: SW is now active!');
                                            resolve();
                                        }
                                    });
                                } else {
                                    resolve();
                                }
                            });
                        }

                        console.log('FCM: SW is active, requesting permission...');
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') {
                            console.warn('FCM: Permission not granted:', permission);
                            return;
                        }

                        console.log('FCM: Getting token with VAPID key...');
                        const token = await getToken(messaging, { 
                            vapidKey: VAPID_KEY, 
                            serviceWorkerRegistration: reg 
                        });
                        if (!token) {
                            console.warn('FCM: No token received.');
                            return;
                        }

                        console.log('FCM: Token received, sending to server:', token.substring(0, 10) + '...');
                        // Simpan FCM token ke server
                        const response = await fetch('/firebase/token', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ token }),
                        });
                        const result = await response.json();
                        console.log('FCM: Server response:', result);
                    } catch (e) {
                        console.error('FCM setup error:', e);
                    }
                });
        } else {
            console.warn('FCM: SW or Notifications not supported/blocked', { 
                sw: 'serviceWorker' in navigator, 
                perm: Notification.permission 
            });
        }

        // Notifikasi saat browser di foreground
        onMessage(messaging, (payload) => {
            // Kita pakai payload.data karena server sekarang mengirim "Data-only" FCM
            const { title, body, id } = payload.data || {};
            
            if (Notification.permission === 'granted' && title) {
                new Notification(title, { 
                    body: body || '', 
                    icon: '/icons/icon-192x192.png',
                    tag: id || 'lms-notif',
                    renotify: true
                });
            }
            // Sanitize payload to avoid "Illegal invocation" from native objects
            const cleanPayload = JSON.parse(JSON.stringify(payload));
            // Dispatch event ke Alpine untuk update badge notifikasi
            document.dispatchEvent(new CustomEvent('fcm-message', { detail: cleanPayload }));
            
            // Livewire 3 dispatch
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('fcm-message');
            }
        });
        @endauth
    </script>

</head>
<body class="min-h-screen bg-premium-mesh flex font-sans" x-data="{ isSidebarOpen: false }">
    @php
        $user = auth()->user();
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();
    @endphp

    <!-- Mobile Header / Menu Button -->
    <div class="md:hidden fixed top-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-md border-b border-gray-200 z-40 flex items-center justify-between px-4 shadow-sm">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow-sm overflow-hidden">
                <img src="/icons/logo192x192.png" class="w-full h-full object-cover" alt="Logo">
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-gray-800 text-sm tracking-tight leading-none">{{ \App\Models\SiteSetting::get('app_name', 'Pro2Lms') }}</span>
                @if($activePeriod)
                    <span class="text-[10px] font-bold text-indigo-600 uppercase">{{ $activePeriod->name }}</span>
                @endif
            </div>
        </div>
        <button 
            @click="isSidebarOpen = !isSidebarOpen"
            class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition active:scale-95"
        >
            <i data-lucide="menu" x-show="!isSidebarOpen" class="w-6 h-6"></i>
            <i data-lucide="x" x-show="isSidebarOpen" x-cloak class="w-6 h-6"></i>
        </button>
    </div>



    <!-- Overlay for mobile -->
    <div 
        x-show="isSidebarOpen" 
        x-transition.opacity.duration.300ms
        @click="isSidebarOpen = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden"
        x-cloak
    ></div>

    <aside 
        :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-72 text-white flex flex-col shadow-2xl transition-transform duration-300 ease-out md:static md:translate-x-0 md:shadow-none"
        style="background-color: var(--sidebar-bg)"
    >
        <div class="p-6 flex items-center justify-between border-b" style="border-color: var(--sidebar-border)">
            <div class="flex items-center gap-3">
                @if($logo = \App\Models\SiteSetting::get('app_logo'))
                    <img src="{{ Storage::url($logo) }}" class="w-10 h-10 rounded-xl object-cover shadow-lg" alt="Logo">
                @else
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg overflow-hidden">
                        <img src="/icons/logo192x192.png" class="w-full h-full object-cover" alt="Logo">
                    </div>
                @endif
                <div>
                    <div class="text-xl font-bold tracking-tight">{{ \App\Models\SiteSetting::get('app_name', 'Pro2Lms') }}</div>
                    @if($activePeriod)
                        <div class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider mt-0.5">
                            {{ $activePeriod->name }}
                        </div>
                    @else
                        <div class="text-xs text-indigo-300 font-medium tracking-wide">Learning System</div>
                    @endif
                </div>
            </div>
            <livewire:sidebar-notifications />
            <!-- Close button for mobile inside sidebar -->
            <button @click="isSidebarOpen = false" class="md:hidden text-indigo-300 hover:text-white transition p-1 rounded-md hover:bg-[#312e81]">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Utama</div>
            
            <a href="{{ url('/dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ (request()->is('dashboard*') || request()->is('dosen/dashboard*') || request()->is('admin/dashboard*') || request()->is('mahasiswa/dashboard*')) ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
               style="{{ (request()->is('dashboard*') || request()->is('dosen/dashboard*') || request()->is('admin/dashboard*') || request()->is('mahasiswa/dashboard*')) ? 'background-color: var(--primary-color)' : '' }}"
            >
                <i data-lucide="home" class="{{ request()->is('dashboard*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                <span class="font-bold text-sm tracking-tight">Dashboard</span>
            </a>
            
            @if($user && $user->role === 'admin')
                <div class="text-[10px] font-black text-indigo-400/50 uppercase tracking-[0.2em] mb-2 px-4 mt-8">Administrator</div>
                <a href="{{ url('/admin/users') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/users*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/users*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="users" class="{{ request()->is('admin/users*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Manajemen User</span>
                </a>

                <a href="{{ route('admin.layout') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/layout*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/layout*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="palette" class="{{ request()->is('admin/layout*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Kustomisasi Layout</span>
                </a>

                <a href="{{ url('/admin/tickets') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/tickets*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/tickets*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="ticket" class="{{ request()->is('admin/tickets*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight flex-1">Tiket Masuk</span>
                    @php $pendingTicketCount = \App\Models\HelpTicket::where('status', '!=', 'closed')->count(); @endphp
                    @if($pendingTicketCount > 0)
                        <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">{{ $pendingTicketCount }}</span>
                    @endif
                </a>

                <a href="{{ url('/admin/faq') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/faq*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/faq*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="help-circle" class="{{ request()->is('admin/faq*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Kelola FAQ</span>
                </a>

                <div class="text-[10px] font-black text-indigo-400/50 uppercase tracking-[0.2em] mb-2 px-4 mt-8">Laporan & Audit</div>
                <a href="{{ route('admin.periods') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/periods*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/periods*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="calendar" class="{{ request()->is('admin/periods*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Periode Akademik</span>
                </a>
                <a href="{{ route('admin.academic-audit') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/academic-audit*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/academic-audit*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="line-chart" class="{{ request()->is('admin/academic-audit*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Audit Pelaksanaan</span>
                </a>
                <a href="{{ route('admin.grade-recap') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('admin/grade-recap*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('admin/grade-recap*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="award" class="{{ request()->is('admin/grade-recap*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Rekap Nilai Global</span>
                </a>
            @endif

            @if($user && $user->role === 'dosen')
                <div class="text-[10px] font-black text-indigo-400/50 uppercase tracking-[0.2em] mb-2 px-4 mt-8">Akademik</div>
                <a href="{{ url('/dosen/classes') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/classes*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/classes*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="book-open" class="{{ request()->is('dosen/classes*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Kelas Saya</span>
                </a>

                <a href="{{ route('dosen.diskusi.hub') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/diskusi*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/diskusi*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="message-square" class="{{ request()->is('dosen/diskusi*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight flex-1">Pusat Diskusi</span>
                    <livewire:sidebar-discussion-badge />
                </a>
                
                <a href="{{ route('dosen.kelompok.manager') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/kelompok*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/kelompok*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="users-2" class="{{ request()->is('dosen/kelompok*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Kelompok Studi</span>
                </a>

                <a href="{{ route('dosen.presensi.manager') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/presensi*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/presensi*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="calendar-check" class="{{ request()->is('dosen/presensi*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Presensi Kuliah</span>
                </a>
                
                <a href="{{ route('dosen.rekap-nilai') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/rekap-nilai*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/rekap-nilai*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="bar-chart-3" class="{{ request()->is('dosen/rekap-nilai*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors flex-shrink-0"></i>
                    <span class="font-bold text-sm tracking-tight">Rekap Nilai (Semester)</span>
                </a>

                <a href="{{ route('dosen.bank-soal') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('dosen/bank-soal*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('dosen/bank-soal*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="database" class="{{ request()->is('dosen/bank-soal*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors flex-shrink-0"></i>
                    <span class="font-bold text-sm tracking-tight">Bank Soal & Tugas</span>
                </a>
                <a href="https://my-sunrise.com/graph-lms/" target="_blank" class="w-full flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 hover:bg-white/5 group">
                    <i data-lucide="sparkles" class="text-indigo-300 group-hover:text-white w-4 h-4"></i>
                    <span class="font-medium text-sm text-indigo-100 group-hover:text-white">Visualisasi Grafik</span>
                </a>
                <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Sumber Belajar</div>
                <a href="{{ route('ai-assistant') }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('ai-assistant') ? 'text-white' : 'text-indigo-100' }} hover:text-white group" style="{{ request()->is('ai-assistant') ? 'background-color: var(--primary-color)' : '' }}" :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('ai-assistant') ? 'true' : 'false' }} }">
                    <i data-lucide="bot" class="{{ request()->is('ai-assistant') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm">AI Assistant</span>
                </a>

                {{-- Bantuan Section for Dosen --}}
                <div class="text-[10px] font-black text-indigo-400/50 uppercase tracking-[0.2em] mb-2 px-4 mt-8">Bantuan & Feedback</div>
                <a href="{{ url('/support/tickets') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('support/tickets*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('support/tickets*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="ticket" class="{{ request()->is('support/tickets*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Tiket Bantuan</span>
                </a>
                <a href="{{ url('/support/faq') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('support/faq*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('support/faq*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="help-circle" class="{{ request()->is('support/faq*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">FAQ & Feedback</span>
                </a>
            @endif

            @if($user && $user->role === 'mahasiswa')
                <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Pembelajaran</div>
                <a href="{{ url('/mahasiswa/classes') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group {{ request()->is('mahasiswa/classes*') ? 'text-white shadow-md' : 'text-indigo-100' }}"
                   style="{{ request()->is('mahasiswa/classes*') ? 'background-color: var(--primary-color)' : '' }}"
                   :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('mahasiswa/classes*') ? 'true' : 'false' }} }"
                >
                    <i data-lucide="book-open" class="{{ request()->is('mahasiswa/classes*') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm">Kelas & Materi</span>
                </a>

                <a href="{{ route('mahasiswa.diskusi.hub') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group {{ request()->is('mahasiswa/diskusi*') ? 'text-white shadow-md' : 'text-indigo-100' }}"
                   style="{{ request()->is('mahasiswa/diskusi*') ? 'background-color: var(--primary-color)' : '' }}"
                   :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('mahasiswa/diskusi*') ? 'true' : 'false' }} }"
                >
                    <i data-lucide="message-square" class="{{ request()->is('mahasiswa/diskusi*') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm flex-1">Pusat Diskusi</span>
                    <livewire:sidebar-discussion-badge />
                </a>

                <a href="{{ route('mahasiswa.ujians') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group {{ request()->is('mahasiswa/ujians*') ? 'text-white shadow-md' : 'text-indigo-100' }}"
                   style="{{ request()->is('mahasiswa/ujians*') ? 'background-color: var(--primary-color)' : '' }}"
                   :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('mahasiswa/ujians*') ? 'true' : 'false' }} }"
                >
                    <i data-lucide="clipboard-list" class="{{ request()->is('mahasiswa/ujians*') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm">Jadwal Ujian</span>
                </a>

                <a href="{{ url('/mahasiswa/materi-eksternal') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group {{ request()->is('mahasiswa/materi-eksternal*') ? 'text-white shadow-md' : 'text-indigo-100' }}"
                   style="{{ request()->is('mahasiswa/materi-eksternal*') ? 'background-color: var(--primary-color)' : '' }}"
                   :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('mahasiswa/materi-eksternal*') ? 'true' : 'false' }} }"
                >
                    <i data-lucide="link" class="{{ request()->is('mahasiswa/materi-eksternal*') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm">Referensi</span>
                </a>
                <a href="https://my-sunrise.com/graph-lms/" target="_blank" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-white/5 group">
                    <i data-lucide="sparkles" class="text-indigo-300 group-hover:text-white w-4 h-4"></i>
                    <span class="font-medium text-sm text-indigo-100 group-hover:text-white">Visualisasi Grafik</span>
                </a>

                <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Sumber Belajar</div>
                <a href="{{ route('ai-assistant') }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('ai-assistant') ? 'text-white' : 'text-indigo-100' }} hover:text-white group" style="{{ request()->is('ai-assistant') ? 'background-color: var(--primary-color)' : '' }}" :class="{ 'hover:bg-[var(--sidebar-border)]': !{{ request()->is('ai-assistant') ? 'true' : 'false' }} }">
                    <i data-lucide="bot" class="{{ request()->is('ai-assistant') ? 'text-white' : 'text-indigo-300 group-hover:text-white' }} w-4 h-4"></i>
                    <span class="font-medium text-sm">AI Assistant</span>
                </a>

                {{-- Bantuan Section for Mahasiswa --}}
                <div class="text-[10px] font-black text-indigo-400/50 uppercase tracking-[0.2em] mb-2 px-4 mt-8">Bantuan & Feedback</div>
                <a href="{{ url('/support/tickets') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('support/tickets*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('support/tickets*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="ticket" class="{{ request()->is('support/tickets*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">Tiket Bantuan</span>
                </a>
                <a href="{{ url('/support/faq') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group {{ request()->is('support/faq*') ? 'text-white shadow-lg scale-[1.02]' : 'text-indigo-100/70 hover:text-white hover:bg-white/5' }}"
                   style="{{ request()->is('support/faq*') ? 'background-color: var(--primary-color)' : '' }}"
                >
                    <i data-lucide="help-circle" class="{{ request()->is('support/faq*') ? 'text-white' : 'text-indigo-300/50 group-hover:text-indigo-200' }} w-5 h-5 transition-colors"></i>
                    <span class="font-bold text-sm tracking-tight">FAQ & Feedback</span>
                </a>
            @endif
        </nav>
        
        <div class="p-4 border-t bg-[#17153b]" style="border-color: var(--sidebar-border)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold shadow-md" style="background-color: var(--primary-color)">
                        {{ substr($user->name ?? 'G', 0, 1) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-sm truncate">{{ $user->name ?? 'Guest' }}</div>
                        <div class="text-xs text-indigo-300 capitalize">{{ $user->role ?? 'guest' }}</div>
                    </div>
                </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 p-2 rounded-lg hover:bg-red-600 text-indigo-200 hover:text-white transition-all duration-200 text-sm font-medium" style="background-color: var(--sidebar-border)">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-4 md:p-8 overflow-y-auto h-screen pt-20 md:pt-8 transition-all duration-300 text-black">
        <div class="max-w-7xl mx-auto w-full transition-all duration-300">
            {{ $slot }}
        </div>
    </main>

    <!-- Alpine.js (Bundled in Livewire 3, no CDN needed) -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
    <script>
        function renderMath() {
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '\\[', right: '\\]', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false}
                    ],
                    throwOnError: false,
                    ignoredClasses: ['mceNonEditable', 'tox', 'mathlive-input']
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(renderMath, 100);
        });
        document.addEventListener('livewire:navigated', () => {
             setTimeout(renderMath, 100);
        });
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ respond, succeed }) => {
                succeed(({ responses }) => {
                    setTimeout(() => {
                        renderMath();
                    }, 50);
                });
            });
        });
    </script>
    {{-- Exam Engine: loaded globally so Alpine always has examSecurity registered --}}
    @vite('resources/js/exam-engine.js')
    @livewireScripts
    @stack('scripts')
    
    
    {{-- Floating AI Assistant (Lecturer & Student) --}}
    @if(auth()->check() && in_array(auth()->user()->role, ['mahasiswa', 'dosen']) && !request()->routeIs('mahasiswa.ujians.take'))
        @livewire('ai-chat-widget')
    @endif
    {{-- Global Alert Listeners --}}
    <script>
        // Use window property to avoid 'already declared' SyntaxError on Livewire SPA re-injection
        window.triggerAlerts = function() {
            @if(session('success'))
                window.showSessionAlert('success', "{{ session('success') }}");
            @endif
            @if(session('error'))
                window.showSessionAlert('error', "{{ session('error') }}");
            @endif
            @if(session('message'))
                window.showSessionAlert('success', "{{ session('message') }}");
            @endif
            @if(session('warning'))
                window.showSessionAlert('warning', "{{ session('warning') }}");
            @endif
        };

        // For initial load (use addEventListener only once)
        if (!window._alertListenerSet) {
            window._alertListenerSet = true;
            document.addEventListener('DOMContentLoaded', window.triggerAlerts);
            document.addEventListener('livewire:navigated', window.triggerAlerts);
        }

        // Bridge for Livewire 3 dispatch
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (event) => {
                // event is an array of data in Livewire 3
                const data = Array.isArray(event) ? event[0] : event;
                window.dispatchEvent(new CustomEvent('swal', { detail: data }));
            });
        });
    </script>
</body>
</html>
