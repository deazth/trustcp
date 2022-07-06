<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_units', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('lob');
            $table->integer('pporgunit');
            $table->string('pporgunitdesc');
            $table->integer('ppsuborg');
            $table->string('ppsuborgunitdesc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_units');
    }
}
