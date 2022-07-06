<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompGroupLovgpPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comp_group_lovgp', function (Blueprint $table) {
            $table->integer('comp_group_id')->unsigned()->index();
            $table->foreign('comp_group_id')->references('id')->on('comp_groups')->onDelete('cascade');
            $table->integer('lovgp_id')->unsigned()->index();
            $table->foreign('lovgp_id')->references('id')->on('lovgps')->onDelete('cascade');
            $table->primary(['comp_group_id', 'lovgp_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comp_group_lovgp');
    }
}
