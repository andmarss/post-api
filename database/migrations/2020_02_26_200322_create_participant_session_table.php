<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateParticipantSessionTable extends \App\System\Database\Migration
{
    protected $table = 'participant_session';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->unsignedInteger('SessionID');
            $builder->unsignedInteger('ParticipantID');
            $builder->unique(['SessionID', 'ParticipantID']);
        });
    }

    public function down()
    {
        $this->dropIfExists(function (SchemaBuilder $builder) {
            $builder->dropUnique(['SessionID', 'ParticipantID']);
        });
    }
}