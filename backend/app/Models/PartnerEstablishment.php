<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerEstablishment extends Model
{
    use HasFactory;

    protected $table = 'partner_establishments';

    protected $fillable = [
        'name',
        'municipality_id',
        'mto_name',
        'mto_id',
        'category',
        'description',
        'status',
        'partner_code',
        'city',
        'province',
        'representative',
        'contact_number',
        'email',
        'logo',
        'account_status',
        'partnership_status',
        'approved_at',
    ];

    protected $casts = [
        'municipality_id' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'partner_establishment_voucher', 'partner_establishment_id', 'voucher_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'partner_staff', 'partner_establishment_id', 'user_id');
    }
}
