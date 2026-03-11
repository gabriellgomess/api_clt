<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'dataprev_request_logs';

    protected $fillable = [
        'client_token',
        'endpoint',
        'http_status',
        'results_count',
    ];
}
