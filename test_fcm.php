<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

echo "--- TESTING FCM SEND TO USER ID 3 ---\n";
try {
    $fcm = app(FirebaseNotificationService::class);
    $status = $fcm->sendToUser(3, "Test Notification", "This is a direct test from CLI");
    
    if ($status) {
        echo "SUCCESS: FCM sent successfully!\n";
    } else {
        echo "FAILED: FCM send returned false. Check logs or check if user has token.\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: Exception caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "--- CHECKING LOGS FOR NEW MESSAGES ---\n";
$log = shell_exec('powershell -Command "Get-Content -Path \'storage/logs/laravel.log\' -Tail 10"');
echo "Latest log entries:\n$log\n";
