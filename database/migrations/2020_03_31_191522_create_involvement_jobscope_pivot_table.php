<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvolvementJobscopePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('involvement_jobscope', function (Blueprint $table) {
            $table->integer('involvement_id')->unsigned()->index();
            $table->foreign('involvement_id')->references('id')->on('involvements')->onDelete('cascade');
            $table->integer('jobscope_id')->unsigned()->index();
            $table->foreign('jobscope_id')->references('id')->on('jobscopes')->onDelete('cascade');
            $table->primary(['involvement_id', 'jobscope_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('involvement_jobscope');
    }
}
