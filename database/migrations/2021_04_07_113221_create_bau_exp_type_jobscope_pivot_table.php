<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBauExpTypeJobscopePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bau_exp_type_jobscope', function (Blueprint $table) {
            $table->integer('bau_exp_type_id')->unsigned()->index();
            $table->foreign('bau_exp_type_id')->references('id')->on('bau_exp_types')->onDelete('cascade');
            $table->integer('jobscope_id')->unsigned()->index();
            $table->foreign('jobscope_id')->references('id')->on('jobscopes')->onDelete('cascade');
            $table->primary(['bau_exp_type_id', 'jobscope_id'], 'betj_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bau_exp_type_jobscope');
    }
}
