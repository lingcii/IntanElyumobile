<?php

namespace Database\Seeders;

use App\Models\TourismSeason;
use Illuminate\Database\Seeder;

class TourismSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seasons = [
            [
                'name'                     => 'Amihan Surfing Season',
                'slug'                     => 'amihan-surfing-season',
                'type'                     => 'annual_season',
                'start_month'              => 10, // October
                'end_month'                => 3,  // March
                'applicable_days'          => null,
                'municipality'             => 'San Juan',
                'fare_surcharge_percent'   => 20.00,
                'cost_multiplier'          => 1.20,
                'traffic_delay_multiplier' => 1.25,
                'description'              => 'Peak North Swell surfing season in San Juan and Bacnotan. High tourist arrivals, surf competitions, and beach events.',
                'is_active'                => true,
            ],
            [
                'name'                     => 'Summer Beach & Eco Falls Peak',
                'slug'                     => 'summer-beach-eco-falls-peak',
                'type'                     => 'annual_season',
                'start_month'              => 3, // March
                'end_month'                => 5, // May
                'applicable_days'          => null,
                'municipality'             => null, // Province-wide
                'fare_surcharge_percent'   => 20.00,
                'cost_multiplier'          => 1.20,
                'traffic_delay_multiplier' => 1.20,
                'description'              => 'Philippine summer peak travel. High visitor volume at Tangadan Falls, Immuki Island, and coastal resorts across La Union.',
                'is_active'                => true,
            ],
            [
                'name'                     => 'Weekend Influx Surge',
                'slug'                     => 'weekend-influx-surge',
                'type'                     => 'weekend_surge',
                'start_month'              => null,
                'end_month'                => null,
                'applicable_days'          => ['Friday', 'Saturday', 'Sunday'],
                'municipality'             => null,
                'fare_surcharge_percent'   => 15.00,
                'cost_multiplier'          => 1.15,
                'traffic_delay_multiplier' => 1.20,
                'description'              => 'Regular weekend influx of road-trippers and vacationers from Metro Manila and Baguio via TPLEX.',
                'is_active'                => true,
            ],
            [
                'name'                     => 'Holy Week (Semana Santa)',
                'slug'                     => 'holy-week-semana-santa',
                'type'                     => 'event_festival',
                'start_month'              => 3,
                'end_month'                => 4,
                'applicable_days'          => null,
                'municipality'             => null,
                'fare_surcharge_percent'   => 25.00,
                'cost_multiplier'          => 1.25,
                'traffic_delay_multiplier' => 1.35,
                'description'              => 'Highest pilgrim and holidaymaker congestion of the year. Heavy traffic on MacArthur Highway.',
                'is_active'                => true,
            ],
            [
                'name'                     => 'Sillag Festival of Lights',
                'slug'                     => 'sillag-festival-of-lights',
                'type'                     => 'event_festival',
                'start_month'              => 4,
                'end_month'                => 4,
                'applicable_days'          => null,
                'municipality'             => 'San Fernando',
                'fare_surcharge_percent'   => 20.00,
                'cost_multiplier'          => 1.20,
                'traffic_delay_multiplier' => 1.30,
                'description'              => 'Annual evening illumination festival at Poro Point Lighthouse and Baywalk with concerts and pyro-musical shows.',
                'is_active'                => true,
            ],
        ];

        foreach ($seasons as $season) {
            TourismSeason::updateOrCreate(
                ['slug' => $season['slug']],
                $season
            );
        }
    }
}
