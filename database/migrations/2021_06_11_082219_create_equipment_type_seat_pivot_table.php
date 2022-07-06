<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEquipmentTypeSeatPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('seats', function (Blueprint $table) {
          $table->id()->change();
      });
      
        Schema::create('equipment_type_seat', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_type_id')->index();
            $table->foreign('equipment_type_id')->references('id')->on('equipment_types')->onDelete('cascade');
            $table->unsignedBigInteger('seat_id')->index();
            $table->foreign('seat_id')->references('id')->on('seats')->onDelete('cascade');
            $table->primary(['equipment_type_id', 'seat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_type_seat');
    }
}
