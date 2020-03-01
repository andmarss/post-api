<?php

namespace App\System\Console\Commands;

use App\System\Console\Command;

class DBSeedCommand extends Command
{
    public const NAME = 'db:seed';

    public function execute(): void
    {
        $databaseSeeder = "Seeds\\DatabaseSeeder";

        if (class_exists($databaseSeeder)) {
            $this->info('Начинается загрузка сидеров');

            (new $databaseSeeder())->run();

            $this->info('Сидеры успешно загружены');
        } else {
            $this->error('Сидеры не были загружены, т.к. отсутствует файл DatabaseSeeder.php');
            die;
        }
    }
}