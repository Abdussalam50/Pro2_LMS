<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = config('ai.api_key');
$baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

echo "Listing models using v1beta...\n";
$response = Http::get($baseUrl);
if ($response->successful()) {
    $models = $response->json();
    foreach ($models['models'] as $model) {
        echo "- " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods']) . ")\n";
    }
} else {
    echo "ERROR (v1beta): " . $response->status() . " - " . $response->body() . "\n";
}

$baseUrlV1 = 'https://generativelanguage.googleapis.com/v1/models?key=' . $apiKey;
echo "\nListing models using v1...\n";
$responseV1 = Http::get($baseUrlV1);
if ($responseV1->successful()) {
    $models = $responseV1->json();
    foreach ($models['models'] as $model) {
        echo "- " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods']) . ")\n";
    }
} else {
    echo "ERROR (v1): " . $responseV1->status() . " - " . $responseV1->body() . "\n";
}
