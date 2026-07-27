<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    /**
     * Seeds 5 La Union-themed quests.
     * spot_ids are placeholder IDs — the admin should update them via the backend
     * once the actual tourist spot IDs from the database are confirmed.
     * We use classification-based spot selection logic in QuestController::generate()
     * so the quest still works even if some IDs don't exist.
     */
    public function run(): void
    {
        $quests = [
            [
                'name'           => 'The Heritage Trail',
                'description'    => 'Walk through the rich history of La Union. Visit colonial churches, museums, and ancestral homes that tell the story of the province\'s past.',
                'theme_icon'     => '🏛️',
                'theme_color'    => '#f59e0b',
                'required_hours' => 3.0,
                'spot_ids'       => json_encode([]),  // Admin fills in actual IDs
                'xp_reward'      => 500,
                'badge_name'     => 'Heritage Guardian',
                'badge_icon'     => '🏛️',
                'category'       => 'Historical',
                'is_active'      => true,
            ],
            [
                'name'           => 'Hidden Foodie Quest',
                'description'    => 'Discover the best-kept culinary secrets of La Union. From street food stalls to hidden restaurants serving authentic Ilocano cuisine.',
                'theme_icon'     => '🍜',
                'theme_color'    => '#ef4444',
                'required_hours' => 2.0,
                'spot_ids'       => json_encode([]),
                'xp_reward'      => 350,
                'badge_name'     => 'Foodie Explorer',
                'badge_icon'     => '🍜',
                'category'       => 'Food Destination',
                'is_active'      => true,
            ],
            [
                'name'           => 'Beach Hopper Circuit',
                'description'    => 'Chase waves and sunsets across La Union\'s most beautiful coastlines. From the world-famous Surf City of San Juan to hidden coves up north.',
                'theme_icon'     => '🏖️',
                'theme_color'    => '#06b6d4',
                'required_hours' => 4.0,
                'spot_ids'       => json_encode([]),
                'xp_reward'      => 600,
                'badge_name'     => 'Wave Rider',
                'badge_icon'     => '🌊',
                'category'       => 'Beach',
                'is_active'      => true,
            ],
            [
                'name'           => 'Nature & Waterfalls Quest',
                'description'    => 'Venture into the mountains of La Union to find hidden waterfalls, natural pools, and breathtaking viewpoints far from the tourist crowds.',
                'theme_icon'     => '💧',
                'theme_color'    => '#10b981',
                'required_hours' => 3.5,
                'spot_ids'       => json_encode([]),
                'xp_reward'      => 450,
                'badge_name'     => 'Nature Seeker',
                'badge_icon'     => '🌿',
                'category'       => 'Waterfalls',
                'is_active'      => true,
            ],
            [
                'name'           => 'Surfing & Sports Circuit',
                'description'    => 'For the thrill-seekers! Hit the surf breaks, try cliff jumping, and explore the adventure sports destinations that make La Union a sports paradise.',
                'theme_icon'     => '🏄',
                'theme_color'    => '#8b5cf6',
                'required_hours' => 2.5,
                'spot_ids'       => json_encode([]),
                'xp_reward'      => 400,
                'badge_name'     => 'Adrenaline Chaser',
                'badge_icon'     => '🏄',
                'category'       => 'Adventure',
                'is_active'      => true,
            ],
        ];

        foreach ($quests as $quest) {
            Quest::firstOrCreate(
                ['name' => $quest['name']],
                $quest
            );
        }

        $this->command?->info('✅ QuestSeeder: 5 La Union quests seeded successfully.');
    }
}
