<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

echo "--- CHECKING CLASSES ---\n";
$classes = Kelas::all();
foreach ($classes as $kelas) {
    echo "Class: {$kelas->kelas} (ID: {$kelas->kelas_id})\n";
    
    $usersInClass = User::where(function($query) use ($kelas) {
        $kelasId = $kelas->kelas_id;
        $query->whereHas('mahasiswa', fn($q) => $q->where('kelas_id', $kelasId))
              ->orWhereHas('kelompokAnggota.kelompok', fn($q) => $q->where('kelas_id', $kelasId));
    })->get(['id', 'name', 'fcm_token']);

    echo "  Total users in class: " . $usersInClass->count() . "\n";
    foreach ($usersInClass as $user) {
        $hasToken = $user->fcm_token ? "YES" : "NO";
        echo "    - User: {$user->name} (ID: {$user->id}) | Token: $hasToken\n";
    }
    echo "\n";
}

echo "--- CHECKING FIREBASE CONFIG ---\n";
$config = config('firebase');
echo "Project ID: " . $config['project_id'] . "\n";
echo "Credentials Path: " . $config['credentials'] . "\n";
echo "File Exists: " . (file_exists(base_path($config['credentials'])) ? 'YES' : 'NO') . "\n";
