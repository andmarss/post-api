<?php

namespace Seeds;

use App\System\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->console->confirm('Нужно ли обновить миграции перед заполнением? Это очистит все старые данные.', true)) {
            $this->console->call('migrate:fresh');
        }

        if ($this->console->confirm('Добавить участников (Participant)?', true)) {
            $this->call(ParticipantTableSeeder::class);
        }

        if ($this->console->confirm('Добавить новости (News)?', true)) {
            $this->call(NewsTableSeeder::class);
        }

        if ($this->console->confirm('Добавить сессии (Session)?', true)) {
            $this->call(SessionTableSeeder::class);
        }

        if ($this->console->confirm('Добавить спикеров (Speaker)?', true)) {
            $this->call(SpeakerTableSeeder::class);
        }
    }
}