<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WhatsApp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'whatsapp';
    }
}