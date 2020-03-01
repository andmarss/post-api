<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateParticipantTable extends \App\System\Database\Migration
{
    protected $table = 'Participant';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->string('Email');
            $builder->string('Name');
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}