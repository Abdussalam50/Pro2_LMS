<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GeminiAiService;
use Illuminate\Support\Facades\Log;

echo "Testing Gemini API Connection...\n";
$model = "gemini-1.5-flash";
echo "Model: " . $model . "\n";

$service = new GeminiAiService();
// Manually set model for test
$reflector = new ReflectionProperty($service, 'model');
$reflector->setAccessible(true);
$reflector->setValue($service, $model);

$result = $service->chat([
    ['role' => 'user', 'content' => 'Hello, are you working? Respond with "YES" if you are.']
]);

if (isset($result['error'])) {
    echo "ERROR: " . $result['error'] . "\n";
    if (isset($result['raw_response'])) {
        echo "RAW RESPONSE: " . $result['raw_response'] . "\n";
    }
} else {
    echo "SUCCESS: " . $result['content'] . "\n";
}
