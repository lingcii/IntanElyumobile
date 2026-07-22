<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouristSpotAudit extends Model
{
    protected $table = 'tourist_spot_audit';

    public $timestamps = false;

    protected $fillable = [
        'spot_id',
        'user_id',
        'action',
        'changes',
        'ip_address',
        'user_agent',
    ];

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'spot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
