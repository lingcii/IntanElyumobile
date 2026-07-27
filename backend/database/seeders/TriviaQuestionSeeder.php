<?php

namespace Database\Seeders;

use App\Models\TriviaQuestion;
use Illuminate\Database\Seeder;

class TriviaQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // General La Union trivia (spot_id = null — used as fallback for any spot)
        $general = [
            [
                'spot_id'       => null,
                'question'      => 'What is the capital city of La Union province?',
                'options'       => json_encode(['San Fernando', 'Agoo', 'San Juan', 'Bauang']),
                'correct_index' => 0,
                'difficulty'    => 'easy',
                'fun_fact'      => 'San Fernando is nicknamed "City of My Love" and is a first-class component city of La Union.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'La Union is known as the "Surfing Capital" of which region in the Philippines?',
                'options'       => json_encode(['Northern Luzon', 'Ilocos Region', 'Cagayan Valley', 'Central Luzon']),
                'correct_index' => 1,
                'difficulty'    => 'easy',
                'fun_fact'      => 'The Ilocos Region (Region I) is home to La Union, and San Juan is its world-famous surf hub.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'Which municipality in La Union is the official "Surf City of the North"?',
                'options'       => json_encode(['San Fernando', 'Bauang', 'San Juan', 'Tubao']),
                'correct_index' => 2,
                'difficulty'    => 'easy',
                'fun_fact'      => 'San Juan has hosted international surf competitions and is famous for its consistent waves at Urbiztondo Beach.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'What UNESCO World Heritage Site is found in Agoo, La Union?',
                'options'       => json_encode(['Agoo Basilica', 'St. William Cathedral', 'Paoay Church', 'Nuestra Señora de Caridad']),
                'correct_index' => 0,
                'difficulty'    => 'medium',
                'fun_fact'      => 'The Basilica of Our Lady of Charity in Agoo is a pilgrimage site visited by thousands yearly, especially during Holy Week.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'What is the famous Ilocano delicacy made from fermented fish native to La Union?',
                'options'       => json_encode(['Bagnet', 'Pinakbet', 'Bagoong isda', 'Dinengdeng']),
                'correct_index' => 2,
                'difficulty'    => 'medium',
                'fun_fact'      => 'Bagoong isda (fermented fish paste) is a staple condiment in Ilocano cuisine and a major product of La Union.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'Approximately how many kilometers is La Union\'s coastline along Lingayen Gulf?',
                'options'       => json_encode(['35 km', '55 km', '80 km', '120 km']),
                'correct_index' => 1,
                'difficulty'    => 'hard',
                'fun_fact'      => 'La Union has about 55 km of coastline, offering beaches ranging from the famous surfing beaches to quieter northern coves.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'Which La Union landmark is known as the "Lion\'s Head"?',
                'options'       => json_encode(['A rock formation in Bacnotan', 'A statue in San Fernando', 'A viewpoint in Naguilian', 'The Poro Point lighthouse']),
                'correct_index' => 0,
                'difficulty'    => 'hard',
                'fun_fact'      => 'The Lion\'s Head rock formation in Bacnotan is a beloved local landmark and popular photo spot for tourists.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'What annual cultural festival celebrates the harvest in La Union?',
                'options'       => json_encode(['Bangus Festival', 'Pamulinawen Festival', 'Agoo Semana Santa', 'Rufino Linog Festival']),
                'correct_index' => 3,
                'difficulty'    => 'hard',
                'fun_fact'      => 'The Rufino Linog Festival in San Fernando City celebrates the city\'s patron saint and showcases La Union\'s vibrant cultural heritage.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'What is the traditional Ilocano weaving craft produced in La Union?',
                'options'       => json_encode(['Burnay pottery', 'Abel Iloco weaving', 'Burnay weaving', 'Banig mat weaving']),
                'correct_index' => 1,
                'difficulty'    => 'medium',
                'fun_fact'      => 'Abel Iloco is a traditional hand-woven textile produced in the Ilocos region, known for its intricate patterns and durability.',
            ],
            [
                'spot_id'       => null,
                'question'      => 'The surf break in San Juan, La Union faces which direction, making it ideal for surfing?',
                'options'       => json_encode(['East', 'North', 'West', 'South']),
                'correct_index' => 2,
                'difficulty'    => 'medium',
                'fun_fact'      => 'San Juan\'s west-facing beaches receive consistent swells from the South China Sea, providing reliable waves especially from July to February.',
            ],
        ];

        foreach ($general as $q) {
            TriviaQuestion::firstOrCreate(
                ['question' => $q['question']],
                array_merge($q, ['is_active' => true])
            );
        }

        $this->command->info('✅ TriviaQuestionSeeder: 10 La Union trivia questions seeded.');
    }
}
