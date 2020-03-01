<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateSpeakerTable extends \App\System\Database\Migration
{
    protected $table = 'Speaker';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->string('Name');
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}