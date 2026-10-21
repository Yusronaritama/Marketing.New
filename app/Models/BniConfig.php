<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BniConfig extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'bni_configs';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'secret_key',
        'api_url',
        'va_prefix',
        'amount',
    ];
}
