<?php

namespace Migrations;

use App\System\Database\SchemaBuilder;

class CreateNewsTable extends \App\System\Database\Migration
{
    protected $table = 'News';

    public function up()
    {
        $this->create(function (SchemaBuilder $builder){
            $builder->increments('ID');
            $builder->unsignedInteger('ParticipantId');
            $builder->string('NewsTitle');
            $builder->text('NewsMessage');
            $builder->integer('LikesCounter')->unsigned();
        });
    }

    public function down()
    {
        $this->dropIfExists();
    }
}