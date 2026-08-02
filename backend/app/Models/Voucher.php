<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $table = 'vouchers';

    protected $fillable = [
        'voucher_code',
        'voucher_name',
        'description',
        'image',
        'discount_type',
        'discount_value',
        'required_points',
        'available_quantity',
        'redeemed_quantity',
        'remaining_quantity',
        'municipality_id',
        'partner_establishment',
        'maximum_redemption_per_user',
        'valid_from',
        'expires_at',
        'terms_and_conditions',
        'status',
        'created_by'
    ];

    protected $casts = [
        'discount_value' => 'float',
        'required_points' => 'integer',
        'available_quantity' => 'integer',
        'redeemed_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'valid_from' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
