<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeatBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('seat_id')->index();
            $table->integer('booked_count')->default(0);
            $table->integer('time_slot');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_bookings');
    }
}
