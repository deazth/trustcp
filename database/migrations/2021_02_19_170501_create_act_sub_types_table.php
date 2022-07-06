<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActSubTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('act_sub_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('comp_group_id')->unsigned()->index();
            $table->integer('activity_type_id')->unsigned()->index();
            $table->string('descr', 500);
            $table->integer('added_by');
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('act_sub_types');
    }
}
