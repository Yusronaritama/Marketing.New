<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BNICallback extends Model
{
    use HasFactory;
    protected $table = 'bni_callbacks';

    protected $fillable = [
        'trx_id',
        'virtual_account',
        'payment_ntb',
        'payment_amount',
        'datetime_payment',
        'raw_data',
        'decrypted_data',
    ];
}
