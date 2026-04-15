<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notifikasi;
use App\Models\Kelompok;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (FCM) v1 via HTTP REST API
 * Tidak memerlukan kreait/laravel-firebase — langsung pakai HTTP + service account JWT
 */
class FirebaseNotificationService
{
    private string $projectId;
    private string $credentialsPath;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->projectId       = config('firebase.project_id');
        $this->credentialsPath = base_path(config('firebase.credentials'));
    }

    // ---------- PUBLIC API ----------

    /** Kirim notifikasi ke satu user berdasarkan fcm_token di tabel users */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = User::find($userId);
        if (!$user) {
            Log::warning("FCM: User $userId not found.");
            return false;
        }

        if (!$user->fcm_token) {
            Log::warning("FCM: User $userId ({$user->name}) has no FCM token. Skipping.");
            return false;
        }

        $status = $this->sendFcm($user->fcm_token, $title, $body, $data);
        
        // Simpan ke DB agar muncul di sidebar
        $this->createDbNotifikasi($userId, $data['type'] ?? 'diskusi_dosen', $data + ['title' => $title, 'body' => $body]);
        
        return $status;
    }

    /** Kirim ke semua anggota kelompok */
    public function sendToKelompok(string $kelompokId, string $title, string $body, array $data = [], ?int $excludeUserId = null): void
    {
        $kelompok = Kelompok::with('users')->find($kelompokId);
        if (!$kelompok) return;

        // Deduplicate users in case of multiple pivot entries
        $targetUsers = $kelompok->users->unique('id');

        foreach ($targetUsers as $user) {
            if ($excludeUserId && $user->id === $excludeUserId) continue;
            
            if ($user->fcm_token) {
                $this->sendFcm($user->fcm_token, $title, $body, $data);
            } else {
                Log::warning("FCM: User {$user->id} ({$user->name}) in group {$kelompokId} has no token.");
            }
            
            // Simpan ke DB untuk setiap anggota (kecuali pengirim)
            $this->createDbNotifikasi($user->id, $data['type'] ?? 'diskusi_kelompok', $data + ['title' => $title, 'body' => $body]);
        }
    }

    /** Kirim ke semua mahasiswa dalam satu kelas (untuk pengumuman per kelas, jika ada) */
    public function sendToKelas(string $kelasId, string $title, string $body, array $data = [], ?int $excludeUserId = null): void
    {
        // Ambil semua user yang terdaftar di kelas ini (via pivot kelas_mahasiswa atau Kelompok atau Dosen)
        $users = User::whereNotNull('fcm_token')
            ->where(function($query) use ($kelasId) {
                $query->whereHas('mahasiswa', fn($q) => $q->whereHas('kelass', fn($q2) => $q2->where('kelas_mahasiswa.kelas_id', $kelasId)))
                      ->orWhereHas('kelompokAnggota.kelompok', fn($q) => $q->where('kelas_id', $kelasId))
                      ->orWhereHas('dosenData.mataKuliah.kelas', fn($q) => $q->where('kelas.kelas_id', $kelasId));
            })
            ->select('users.*')
            ->get()
            ->unique('id'); // Deduplicate recipients

        foreach ($users as $user) {
            if ($excludeUserId && $user->id === $excludeUserId) continue;

            $this->sendFcm($user->fcm_token, $title, $body, $data);
            $this->createDbNotifikasi($user->id, $data['type'] ?? 'info', $data + ['title' => $title, 'body' => $body]);
        }
    }

    /** Kirim ke SEMUA mahasiswa yang diajar oleh seorang dosen */
    public function sendToSemuaMahasiswaDosen(string $dosenId, string $title, string $body, array $data = []): void
    {
        // Cari semua kelas yang diajar dosen ini via MataKuliah
        $kelasIds = \App\Models\Kelas::whereHas('mataKuliah', fn($q) => $q->where('dosen_id', $dosenId))
            ->pluck('kelas_id');

        if ($kelasIds->isEmpty()) return;

        // Ambil user unik yang terdaftar di kelas-kelas tersebut (via pivot atau kelompok)
        $users = User::whereNotNull('fcm_token')
            ->where(function($query) use ($kelasIds) {
                $query->whereHas('mahasiswa', fn($q) => $q->whereHas('kelass', fn($q2) => $q2->whereIn('kelas_mahasiswa.kelas_id', $kelasIds)))
                      ->orWhereHas('kelompokAnggota.kelompok', fn($q) => $q->whereIn('kelas_id', $kelasIds));
            })
            ->select('users.*')
            ->get()
            ->unique('id'); // Deduplicate recipients

        foreach ($users as $user) {
            $this->sendFcm($user->fcm_token, $title, $body, $data);
            $this->createDbNotifikasi($user->id, $data['type'] ?? 'info', $data);
        }
    }

    /** Simpan notifikasi ke database MySQL */
    public function createDbNotifikasi(int $userId, string $tipe, array $data): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $userId,
            'tipe'    => $tipe,
            'data'    => $data,
            'dibaca'  => false,
        ]);
    }

    // ---------- PRIVATE ----------

    private function sendFcm(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $payload = [
                'message' => [
                    'token' => $token,
                    // Hilangkan blok notification agar tidak diauto-display oleh browser
                    'data' => array_merge(array_map('strval', $data), [
                        'title' => (string) $title,
                        'body'  => (string) $body,
                    ]),
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'fcm_options' => [
                            'link' => $data['url'] ?? '/'
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if (!$response->successful()) {
                Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body(), 'token' => substr($token, 0, 20)]);
                return false;
            }

            Log::info('FCM send success', ['token' => substr($token, 0, 20), 'title' => $title]);
            return true;
        } catch (\Throwable $e) {
            Log::error('FCM error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate OAuth2 access token dari service account JSON
     * Menggunakan JWT yang ditandatangani dengan private key
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) return $this->accessToken;

        $credentials = json_decode(file_get_contents($this->credentialsPath), true);
        $now = time();

        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = "$header.$payload";
        openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');
        $jwt = "$signingInput." . base64url_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        $this->accessToken = $response->json('access_token');
        return $this->accessToken;
    }
}

// Helper untuk base64url encoding (tanpa padding)
if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
