<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRedemption extends Model
{
    protected $table = 'point_redemptions';

    protected $fillable = [
        'user_id',
        'type',
        'points_cost',
        'voucher_code',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
