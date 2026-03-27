<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Partymeister\Slides\Models\Transition;

class AddClientTypeToTransitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transitions', function (Blueprint $table) {
            $table->string('client_type')->after('name');
            $table->string('identifier')->change();
        });
        foreach (Transition::get() as $transition) {
            $transition->client_type = 'screens';
            $transition->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transitions', function (Blueprint $table) {
            $table->dropColumn('client_type');
            $table->integer('identifier')->charset('')->change();
        });
    }
}
