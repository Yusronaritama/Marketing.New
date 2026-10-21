<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/health', 'GET')
);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getContent() . "\n";

// Test BNI API
$testRequest = Illuminate\Http\Request::create('/api/bni/pending-users', 'GET');
$testRequest->headers->set('Authorization', 'Bearer pmbubpkarawang');

$testResponse = $kernel->handle($testRequest);

echo "\nBNI API Test:\n";
echo "Status: " . $testResponse->getStatusCode() . "\n";
echo "Body: " . $testResponse->getContent() . "\n";
