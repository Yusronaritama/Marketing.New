<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Jika user belum login, JANGAN ke /login
        return 'https://penmabar.ubpkarawang.ac.id/';
    }
}
