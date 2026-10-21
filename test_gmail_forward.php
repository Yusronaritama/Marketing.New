<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "=== Testing Gmail via Local Forward ===\n";
echo "Using: localhost:2587 -> smtp.gmail.com:587\n\n";

// Test 1: Check configuration
echo "1. Current Mail Configuration:\n";
echo "   Host: " . config('mail.mailers.smtp.host') . "\n";
echo "   Port: " . config('mail.mailers.smtp.port') . "\n";
echo "   Encryption: " . config('mail.mailers.smtp.encryption') . "\n";

// Test 2: Try socket connection directly
echo "\n2. Testing socket connection:\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$fp = @stream_socket_client('tcp://localhost:2587', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

if ($fp) {
    echo "   ✓ Socket connected to localhost:2587\n";
    
    // Read banner
    $banner = fread($fp, 1024);
    echo "   Banner: " . trim($banner) . "\n";
    
    // Try EHLO
    fwrite($fp, "EHLO localhost\r\n");
    $ehlo = fread($fp, 1024);
    echo "   EHLO Response: " . trim($ehlo) . "\n";
    
    fclose($fp);
} else {
    echo "   ✗ Socket failed: $errstr ($errno)\n";
}

// Test 3: Try sending email
echo "\n3. Attempting to send email via Laravel:\n";

try {
    Mail::raw('Test email via local forward', function($message) {
        $message->to('admin@ubpkarawang.ac.id')
                ->subject('Test - Gmail Local Forward')
                ->from('marketing@ubpkarawang.ac.id', 'PMB UBP');
    });
    
    echo "   ✓ SUCCESS! Email sent successfully!\n";
    
} catch (Exception $e) {
    echo "   ✗ FAILED: " . $e->getMessage() . "\n";
    
    // Detail error analysis
    $error = $e->getMessage();
    if (strpos($error, 'Connection refused') !== false) {
        echo "   → Port forward not working. Check socat/haproxy/nginx.\n";
    } elseif (strpos($error, 'Connection timed out') !== false) {
        echo "   → Forward connected but Gmail not responding.\n";
    } elseif (strpos($error, 'Authentication') !== false) {
        echo "   → Connected! But authentication failed. Check credentials.\n";
    }
}
