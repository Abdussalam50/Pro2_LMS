<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$notifs = \App\Models\Notifikasi::orderBy('created_at', 'desc')->take(5)->get();
foreach($notifs as $n) {
    echo "ID: {$n->notifikasi_id} | User: {$n->user_id} | Tipe: {$n->tipe} | Title: " . ($n->data['title'] ?? 'N/A') . " | Time: {$n->created_at}\n";
}
echo "Total Notifs: " . \App\Models\Notifikasi::count() . "\n";
