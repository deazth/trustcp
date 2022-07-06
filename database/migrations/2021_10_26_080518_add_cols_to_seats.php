<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToSeats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('seats', function (Blueprint $table) {
        $table->unsignedBigInteger('floor_id');
        $table->unsignedBigInteger('building_id');
      });

      Schema::table('user_seat_bookings', function (Blueprint $table) {
        $table->unsignedBigInteger('floor_section_id')->index();
        $table->unsignedBigInteger('floor_id')->index();
        $table->unsignedBigInteger('building_id')->index();
        $table->foreign('seat_id', 'usb_seat')->references('id')->on('seats')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('seats', function (Blueprint $table) {
        $table->dropColumn('floor_id');
        $table->dropColumn('building_id');
      });

      Schema::table('user_seat_bookings', function (Blueprint $table) {
        $table->dropColumn('floor_id');
        $table->dropColumn('floor_section_id');
        $table->dropColumn('building_id');
      });
    }
}
