<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateSessionTable extends \App\System\Database\Migration
{
    protected $table = 'Session';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->string('Name');
            $builder->dateTime('TimeOfEvent');
            $builder->mediumText('Description');
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}