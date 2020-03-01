<?php

namespace Seeds;

use App\Models\Participant;
use App\System\Database\Seeder;
use App\System\Factory\Generator;

class ParticipantTableSeeder extends Seeder
{
    public function run(): void
    {
        factory(Participant::class, 10)->create(function (Generator $faker){
            return [
                'Email' => $faker->safeEmail,
                'Name'  => $faker->firstNameLastName($faker->randomBool)
            ];
        });
    }
}