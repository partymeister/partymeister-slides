<?php

use Culpa\Database\Schema\Blueprint;
use Culpa\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateTransitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transitions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('identifier');
            $table->integer('default_duration');

            $table->createdBy();
            $table->updatedBy();
            $table->deletedBy(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transitions');
    }
}
