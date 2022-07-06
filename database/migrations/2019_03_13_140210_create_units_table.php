<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
          $table->increments('id');
          $table->timestamps();
          $table->integer('lob');
          $table->integer('pporgunit');
          $table->string('pporgunitdesc');
          $table->boolean('allowed')->default(false);
          $table->integer('comp_group_id')->nullable();
          $table->decimal('friday_hours', 5, 2)->default(7.5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
}
