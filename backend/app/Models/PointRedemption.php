<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRedemption extends Model
{
    protected $table = 'point_redemptions';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'type',
        'points_cost',
        'voucher_code',
        'qr_token',
        'status',
        'voucher_expires_at',
        'qr_expires_at',
        'claimed_at',
        'redeemed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'voucher_expires_at' => 'datetime',
        'qr_expires_at' => 'datetime',
        'claimed_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
