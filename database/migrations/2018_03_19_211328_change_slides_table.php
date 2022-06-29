<?php

use Culpa\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->createdBy();
            $table->updatedBy();
            $table->deletedBy(true);
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
