<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientToken extends Model
{
    protected $table = 'dataprev_client_tokens';

    protected $fillable = [
        'alias',
        'token',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
