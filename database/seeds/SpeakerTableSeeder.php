<?php

namespace Seeds;

use App\Models\Session;
use App\Models\Speaker;
use App\System\Database\Seeder;
use App\System\Factory\Generator;

class SpeakerTableSeeder extends Seeder
{
    public function run(): void
    {
        factory(Speaker::class, 10)->create(function (Generator $faker) {
            return [
                'Name' => $faker->name($faker->randomBool)
            ];
        })->each(function (Speaker $speaker) {
            $speaker->sessions()->attach(Session::all()->random()->ID);
        });
    }
}