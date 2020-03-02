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
            /**
             * @var string $name
             */
            $name = $faker->firstNameLastName($faker->randomBool);

            return [
                'Email' => $faker->safeEmail(current(explode(' ', $name))),
                'Name'  => $name
            ];
        });
    }
}