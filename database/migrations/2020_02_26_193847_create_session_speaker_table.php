<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateSessionSpeakerTable extends \App\System\Database\Migration
{
    protected $table = 'session_speaker';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->unsignedInteger('SessionID');
            $builder->unsignedInteger('SpeakerID');
            $builder->unique(['SessionID', 'SpeakerID']);
        });
    }

    public function down()
    {
        $this->dropIfExists(function (SchemaBuilder $builder) {
            $builder->dropUnique(['SessionID', 'SpeakerID']);
        });
    }
}