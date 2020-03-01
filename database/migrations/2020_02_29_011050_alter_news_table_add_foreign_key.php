<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class AlterNewsTableAddForeignKey extends \App\System\Database\Migration
{
    protected $table = 'News';

    public function up()
    {
        $this->alter(function (SchemaBuilder $builder){
            $builder->foreign('ParticipantId')
                ->references('id')
                ->on('Participant')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}