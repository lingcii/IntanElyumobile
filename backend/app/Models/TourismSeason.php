<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourismSeason extends Model
{
    use HasFactory;

    protected $table = 'tourism_seasons';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'start_date',
        'end_date',
        'start_month',
        'end_month',
        'applicable_days',
        'municipality',
        'fare_surcharge_percent',
        'cost_multiplier',
        'traffic_delay_multiplier',
        'description',
        'is_active',
    ];

    protected $casts = [
        'applicable_days'          => 'array',
        'fare_surcharge_percent'   => 'float',
        'cost_multiplier'          => 'float',
        'traffic_delay_multiplier' => 'float',
        'is_active'                => 'boolean',
        'start_date'               => 'date',
        'end_date'                 => 'date',
        'start_month'              => 'integer',
        'end_month'                => 'integer',
    ];

    /**
     * Check if this season applies to a specific Carbon date.
     */
    public function appliesToDate(Carbon $date, ?string $municipality = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Municipality check (if specified on season)
        if ($this->municipality && $municipality) {
            if (strcasecmp($this->municipality, $municipality) !== 0) {
                return false;
            }
        }

        // Day of week check (e.g. Friday, Saturday, Sunday)
        if (!empty($this->applicable_days) && is_array($this->applicable_days)) {
            $dayName = $date->format('l'); // e.g. "Friday"
            $matchesDay = false;
            foreach ($this->applicable_days as $d) {
                if (strcasecmp($d, $dayName) === 0) {
                    $matchesDay = true;
                    break;
                }
            }
            if (!$matchesDay) {
                return false;
            }
        }

        // Fixed date range check (e.g. Holy week or annual festival)
        if ($this->start_date && $this->end_date) {
            $checkYmd = $date->toDateString();
            $startYmd = $this->start_date->toDateString();
            $endYmd = $this->end_date->toDateString();
            return ($checkYmd >= $startYmd && $checkYmd <= $endYmd);
        }

        // Recurring month range check (e.g. October to March: start_month=10, end_month=3)
        if ($this->start_month !== null && $this->end_month !== null) {
            $m = (int) $date->format('n');
            if ($this->start_month <= $this->end_month) {
                return ($m >= $this->start_month && $m <= $this->end_month);
            } else {
                // Crosses new year (e.g. Oct 10 to Mar 3: m >= 10 OR m <= 3)
                return ($m >= $this->start_month || $m <= $this->end_month);
            }
        }

        // Weekend surge rule if type is weekend_surge
        if ($this->type === 'weekend_surge') {
            return $date->isWeekend() || $date->isFriday();
        }

        return false;
    }

    /**
     * Get all active seasons matching a date.
     */
    public static function getActiveSeasonsForDate(?string $dateString = null, ?string $municipality = null)
    {
        try {
            $carbonDate = $dateString ? Carbon::parse($dateString) : now();
        } catch (\Throwable $e) {
            $carbonDate = now();
        }

        $all = static::where('is_active', true)->get();

        return $all->filter(function (self $season) use ($carbonDate, $municipality) {
            return $season->appliesToDate($carbonDate, $municipality);
        })->values();
    }
}
