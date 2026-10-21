<?php

namespace App\Http\Controllers;

use App\Libraries\Boc;
use Illuminate\Http\JsonResponse;

class BocController extends Controller
{
    public function getSiteInfo(): JsonResponse
    {
        $boc = new Boc();
        $result = $boc->core_webservice_get_site_info();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
    