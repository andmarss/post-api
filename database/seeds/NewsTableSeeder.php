<?php

namespace Seeds;

use App\Models\News;
use App\Models\Participant;
use App\System\Database\Seeder;
use App\System\Factory\Generator;

class NewsTableSeeder extends Seeder
{
    public function run(): void
    {
        factory(News::class, 5)->create(function (Generator $faker) {
            return [
                'ParticipantId' => Participant::all()->random()->ID,
                'NewsTitle'     => $faker->words(5),
                'NewsMessage'   => $faker->text(100),
                'LikesCounter'  => $faker->numberBetween(1, 15)
            ];
        });
    }
}