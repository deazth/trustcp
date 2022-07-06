<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSeatCheckinUserPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_checkin_user', function (Blueprint $table) {
            $table->unsignedBigInteger('seat_checkin_id')->index();
            $table->foreign('seat_checkin_id', 'scu_f_sc')->references('id')->on('seat_checkins')->onDelete('cascade');
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id', 'scu_f_user')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['seat_checkin_id', 'user_id'], 'scu_prim');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_checkin_user');
    }
}
