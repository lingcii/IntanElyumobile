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
        'points',
        'completed_activities',
        'level',
        'avatar',
        'google_id',
        'phone',
        'home_location',
        'bio',
        'age',
        'gender',
        'travel_preferences',
        'is_leaderboard_private',
        'last_gps_lat',
        'last_gps_lng',
        'last_gps_ping_at',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'must_change_password',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'two_factor_code',
    ];

    protected $appends = [
        'created_at_formatted',
        'date_created',
        'tourist_number',
    ];

    protected $casts = [
        'xp'                    => 'integer',
        'points'                => 'integer',
        'age'                   => 'integer',
        'two_factor_enabled'    => 'boolean',
        'must_change_password'  => 'boolean',
        'two_factor_expires_at' => 'datetime',
        'email_verified_at'     => 'datetime',
        'last_activity'         => 'datetime',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $now = now();
            if (empty($user->created_at)) {
                $user->created_at = $now;
            }
            if (empty($user->updated_at)) {
                $user->updated_at = $now;
            }
        });
    }

    /** Valid roles in the system */
    public static array $ALL_ROLES = [
        'picto', 'lupto', 'municipal', 'tourist',
        'san_juan_mto', 'san_fernando_mto', 'bauang_mto', 'agoo_mto', 'luna_mto',
        'san_gabriel_mto', 'balaoan_mto', 'aringay_mto', 'rosario_mto', 'bacnotan_mto',
        'naguilian_mto', 'tubao_mto', 'pugo_mto', 'caba_mto', 'santo_tomas_mto',
        'bangar_mto', 'burgos_mto', 'bagulin_mto', 'santol_mto', 'sudipen_mto',
    ];

    /** Municipal (MTO) roles */
    public static array $MUNICIPAL_ROLES = [
        'san_juan_mto', 'san_fernando_mto', 'bauang_mto', 'agoo_mto', 'luna_mto',
        'san_gabriel_mto', 'balaoan_mto', 'aringay_mto', 'rosario_mto', 'bacnotan_mto',
        'naguilian_mto', 'tubao_mto', 'pugo_mto', 'caba_mto', 'santo_tomas_mto',
        'bangar_mto', 'burgos_mto', 'bagulin_mto', 'santol_mto', 'sudipen_mto', 'municipal',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function getRoleAttribute($value)
    {
        return $value === 'pitco' ? 'picto' : $value;
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value === 'picto' ? 'pitco' : $value;
    }

    public function isMunicipal(): bool
    {
        return in_array($this->role, self::$MUNICIPAL_ROLES);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    /**
     * Recalculate and persist user level based on current XP.
     */
    public function recalculateLevel(): int
    {
        $xp = (int) ($this->xp ?? $this->points ?? 0);
        $level = (int) floor(max(0, $xp) / 1000) + 1;
        if ((int)($this->level ?? 1) !== $level) {
            $this->level = $level;
            $this->save();
        }
        return $level;
    }

    /**
     * Deduct Points for vouchers/rewards without reducing XP or Level.
     */
    public function deductPoints(int $amount): int
    {
        $currentPoints = (int) ($this->points ?? 0);
        $newPoints = max(0, $currentPoints - $amount);
        $this->points = $newPoints;
        $this->save();

        return $newPoints;
    }

    /**
     * Deduct XP and re-derive the level.
     */
    public function deductXp(int $amount): int
    {
        $currentXp = (int) ($this->xp ?? 0);
        $newXp = max(0, $currentXp - $amount);
        $newLevel = (int) floor($newXp / 1000) + 1;

        $this->xp = $newXp;
        $this->level = $newLevel;
        $this->save();

        return $newXp;
    }

    /**
     * Add XP and re-derive the level.
     */
    public function addXp(int $amount): int
    {
        $currentXp = (int) ($this->xp ?? 0);
        $newXp = $currentXp + $amount;
        $newLevel = (int) floor($newXp / 1000) + 1;

        $this->xp = $newXp;
        $this->level = $newLevel;
        $this->save();

        return $newXp;
    }

    /**
     * Get sequential tourist number strictly starting from 1 for tourists.
     */
    public function getTouristNumber(): int
    {
        $count = static::where('role', 'tourist')
            ->where('id', '<=', $this->id)
            ->count();

        return $count > 0 ? $count : 1;
    }

    public function getTouristNumberAttribute(): int
    {
        return $this->getTouristNumber();
    }

    public function getCreatedAtFormattedAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->format('M d, Y h:i A') : null;
    }

    public function getDateCreatedAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->format('M d, Y h:i A') : null;
    }

    /**
     * Reconcile and persist the exact distinct places visited count for this user.
     */
    public function syncPlacesVisitedCount(): int
    {
        $distinctSpots = [];
        try {
            $itemSpotIds = \Illuminate\Support\Facades\DB::table('itinerary_items')
                ->join('itineraries', 'itinerary_items.itinerary_id', '=', 'itineraries.id')
                ->where('itineraries.user_id', $this->id)
                ->whereNotNull('itinerary_items.tourist_spot_id')
                ->where(function ($q) {
                    $q->where('itinerary_items.is_visited', true)
                      ->orWhereNotNull('itinerary_items.visited_at')
                      ->orWhere('itineraries.status', 'completed');
                })
                ->pluck('itinerary_items.tourist_spot_id')
                ->all();
            foreach ($itemSpotIds as $sid) {
                if ((int) $sid > 0) {
                    $distinctSpots[(int) $sid] = true;
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                $pointSpotIds = \Illuminate\Support\Facades\DB::table('user_points')
                    ->where('user_id', $this->id)
                    ->whereIn('source', ['check_in', 'ar_checkin'])
                    ->whereNotNull('spot_id')
                    ->pluck('spot_id')
                    ->all();
                foreach ($pointSpotIds as $sid) {
                    if ((int) $sid > 0) {
                        $distinctSpots[(int) $sid] = true;
                    }
                }
            }
        } catch (\Throwable $e) {}

        $trueCount = count($distinctSpots);

        // Preserve non-zero count only if user had no itinerary items left (e.g. deleted all completed trips)
        if ($trueCount === 0 && (int) ($this->completed_activities ?? 0) > 0) {
            try {
                $hasAnyItineraries = \Illuminate\Support\Facades\DB::table('itineraries')
                    ->where('user_id', $this->id)
                    ->exists();
                if ($hasAnyItineraries) {
                    $trueCount = 0;
                } else {
                    $trueCount = (int) $this->completed_activities;
                }
            } catch (\Throwable $e) {
                $trueCount = (int) $this->completed_activities;
            }
        }

        if ((int) ($this->completed_activities ?? 0) !== $trueCount) {
            try {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $this->id)
                    ->update(['completed_activities' => $trueCount]);
                $this->completed_activities = $trueCount;
                \Illuminate\Support\Facades\Cache::forget("rank:user:{$this->id}");
            } catch (\Throwable $e) {}
        }

        return $trueCount;
    }

    /**
     * Reconcile spendable points and total XP against redeemed vouchers so all endpoints return identical values.
     */
    public function syncPointsBalance(): int
    {
        $rawPoints = (int) ($this->points ?? 0);
        $rawXp = (int) ($this->xp ?? 0);
        $userXp = max($rawXp, $rawPoints);
        $redeemedTotal = 0;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $redeemedTotal = (int) \Illuminate\Support\Facades\DB::table('point_redemptions')
                    ->where('user_id', $this->id)
                    ->where(function ($q) {
                        $q->whereNull('status')
                          ->orWhereRaw("LOWER(status) != 'cancelled'");
                    })
                    ->sum('points_cost');
            }
        } catch (\Throwable $e) {}

        if ($redeemedTotal > 0 && $rawPoints === $userXp && $userXp > 0) {
            $userPoints = max(0, $userXp - $redeemedTotal);
        } elseif ($redeemedTotal === 0 && $rawPoints === 0 && $rawXp > 0) {
            $userPoints = $rawXp;
        } else {
            $userPoints = max(0, $rawPoints);
        }

        $newLevel = (int) floor(max(0, $userXp) / 1000) + 1;

        if ($rawPoints !== $userPoints || $rawXp !== $userXp || (int) ($this->level ?? 1) !== $newLevel) {
            try {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $this->id)
                    ->update([
                        'points' => $userPoints,
                        'xp'     => $userXp,
                        'level'  => $newLevel,
                    ]);
                $this->points = $userPoints;
                $this->xp = $userXp;
                $this->level = $newLevel;
                \Illuminate\Support\Facades\Cache::forget("rank:user:{$this->id}");
            } catch (\Throwable $e) {}
        }

        return $userPoints;
    }
}
