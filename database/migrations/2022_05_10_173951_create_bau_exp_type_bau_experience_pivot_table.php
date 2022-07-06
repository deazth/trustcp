<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBauExpTypeBauExperiencePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bau_exp_type_bau_experience', function (Blueprint $table) {
            $table->unsignedInteger('bau_exp_type_id')->index();
            $table->foreign('bau_exp_type_id')->references('id')->on('bau_exp_types')->onDelete('cascade');
            $table->unsignedInteger('bau_experience_id')->index();
            $table->foreign('bau_experience_id')->references('id')->on('bau_experiences')->onDelete('cascade');
            $table->primary(['bau_exp_type_id', 'bau_experience_id'], 'betbe_prim');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bau_exp_type_bau_experience');
    }
}
