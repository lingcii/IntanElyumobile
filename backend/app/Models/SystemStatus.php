<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStatus extends Model
{
    protected $table = 'system_status';
    public $timestamps = false;

    protected $fillable = [
        'service_name', 'status', 'uptime', 'last_checked'
    ];
}
