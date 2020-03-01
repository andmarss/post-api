<?php

namespace Seeds;

use App\Models\Participant;
use App\Models\Session;
use App\System\Database\Seeder;
use App\System\Factory\Generator;

class SessionTableSeeder extends Seeder
{
    public function run(): void
    {
        factory(Session::class, 10)->create(function (Generator $faker){
            return [
                'Name' => $faker->words,
                'TimeOfEvent' => $faker->dateTime('now', '+ '. $faker->numberBetween(15,45) .' days'),
                'Description' => $faker->text(100),
                'ParticipantMaxNumber' => $faker->numberBetween(5, 20)
            ];
        })->each(function (Session $session) {
            $session->participants()->attach(Participant::all()->random()->ID);
        });
    }
}