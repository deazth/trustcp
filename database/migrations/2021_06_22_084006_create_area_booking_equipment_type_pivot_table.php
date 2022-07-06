<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAreaBookingEquipmentTypePivotTable extends Migration
{
    /**
     * Run the migrations.
     * this is for extra equipment request
     * @return void
     */
    public function up()
    {
        Schema::create('area_booking_equipment_type', function (Blueprint $table) {
            $table->unsignedBigInteger('area_booking_id')->index();
            $table->foreign('area_booking_id')->references('id')->on('area_bookings')->onDelete('cascade');
            $table->unsignedBigInteger('equipment_type_id')->index();
            $table->foreign('equipment_type_id')->references('id')->on('equipment_types')->onDelete('cascade');
            $table->primary(['area_booking_id', 'equipment_type_id'], 'abet_prim');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_booking_equipment_type');
    }
}
