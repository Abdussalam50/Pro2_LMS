<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$usersWithToken = User::whereNotNull('fcm_token')->get(['id', 'name', 'email', 'fcm_token']);

echo "Total users with FCM token: " . $usersWithToken->count() . "\n";
foreach ($usersWithToken as $user) {
    echo "ID: {$user->id} | Name: {$user->name} | Token: " . substr($user->fcm_token, 0, 20) . "...\n";
}
