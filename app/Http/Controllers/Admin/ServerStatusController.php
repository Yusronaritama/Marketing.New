<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class ServerStatusController extends Controller
{
    /**
     * URL Node.js server — bisa juga ditaruh di .env sebagai NODE_API_URL
     */
    private string $nodeUrl;

    /**
     * Field yang tidak diteruskan ke frontend
     */
    private array $hiddenFields = [
        'error_count_10m',
    ];

    public function __construct()
    {
        $this->nodeUrl = env('NODE_API_URL', 'http://103.23.198.160:3009');
    }

    public function index(): JsonResponse
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get("{$this->nodeUrl}/api/logs/status");

            if ($response->successful()) {
                $data = collect($response->json())
                    ->except($this->hiddenFields)
                    ->all();

                return response()->json($data);
            }

            return response()->json([
                'error'         => 'Node server returned HTTP ' . $response->status(),
                'system_status' => 'critical',
            ], 200);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error'         => 'connection_failed',
                'system_status' => 'offline',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error'         => 'unexpected: ' . $e->getMessage(),
                'system_status' => 'offline',
            ], 200);
        }
    }
}