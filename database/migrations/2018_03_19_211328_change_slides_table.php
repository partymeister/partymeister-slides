<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeSlidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->renameColumn('slide_templates_id', 'slide_template_id');
            $table->renameColumn('content', 'definitions');
            $table->string('slide_type')->after('name');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->renameColumn('slide_template_id', 'slide_templates_id');
            $table->renameColumn('definitions', 'content');
            $table->dropColumn('slide_type');
        });
    }
}
