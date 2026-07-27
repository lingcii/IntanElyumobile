<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $table = 'quests';

    protected $fillable = [
        'name',
        'description',
        'theme_icon',
        'theme_color',
        'required_hours',
        'spot_ids',
        'xp_reward',
        'badge_name',
        'badge_icon',
        'category',
        'is_active',
    ];

    protected $casts = [
        'spot_ids'       => 'array',
        'required_hours' => 'float',
        'xp_reward'      => 'integer',
        'is_active'      => 'boolean',
    ];

    public function completions()
    {
        return $this->hasMany(QuestCompletion::class);
    }

    public function spots()
    {
        return TouristSpot::whereIn('id', $this->spot_ids ?? [])->get();
    }

    /** Scope: only active quests */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: filter by required hours <= given hours */
    public function scopeWithinHours($query, float $hours)
    {
        return $query->where('required_hours', '<=', $hours);
    }
}
