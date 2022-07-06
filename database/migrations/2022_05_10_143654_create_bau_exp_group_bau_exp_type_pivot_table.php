<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBauExpGroupBauExpTypePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bau_exp_group_bau_exp_type', function (Blueprint $table) {
            $table->unsignedBigInteger('bau_exp_group_id')->index();
            $table->foreign('bau_exp_group_id')->references('id')->on('bau_exp_groups')->onDelete('cascade');
            $table->unsignedInteger('bau_exp_type_id')->index();
            $table->foreign('bau_exp_type_id')->references('id')->on('bau_exp_types')->onDelete('cascade');
            $table->primary(['bau_exp_group_id', 'bau_exp_type_id'], 'begbet_prim');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bau_exp_group_bau_exp_type');
    }
}
