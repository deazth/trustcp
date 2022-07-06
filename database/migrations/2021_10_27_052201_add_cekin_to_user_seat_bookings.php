<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCekinToUserSeatBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_seat_bookings', function (Blueprint $table) {
          $table->unsignedBigInteger('seat_checkin_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_seat_bookings', function (Blueprint $table) {
          $table->dropColumn('seat_checkin_id');
        });
    }
}
