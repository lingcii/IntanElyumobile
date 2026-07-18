<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'municipality_id',
        'last_activity',
        'api_token',
        'xp',
        'level',
        'google_id',
        'is_leaderboard_private',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity'     => 'datetime',
        'created_at'        => 'datetime',
    ];

    /** Valid roles in the system */
    public static array $ALL_ROLES = [
        'tourist',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function merchReservations()
    {
        return $this->hasMany(MerchReservation::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeTourist($query)
    {
        return $query->where('role', 'tourist');
    }
}
