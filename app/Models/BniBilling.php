<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BniBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_account',
        'trx_id',
        'user_id',
        'trx_amount',
        'customer_name',
        'customer_email',
        'customer_phone',
        'datetime_expired',
    ];
}
