<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class AlterSessionTableAddMaxParticipantNumberField extends \App\System\Database\Migration
{
    protected $table = 'Session';

    public function up()
    {
        $this->alter(function (SchemaBuilder $builder){
            $builder->integer('ParticipantMaxNumber')->unsigned();
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}