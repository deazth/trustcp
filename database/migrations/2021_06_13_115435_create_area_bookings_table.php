<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('seat_id');
            $table->unsignedInteger('user_id');  // organizer
            $table->boolean('is_long_term')->default(false);
            $table->boolean('has_extra_req')->default(false);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('event_name', 250);
            $table->text('user_remark')->nullable();
            $table->string('status', 20);
            $table->unsignedInteger('admin_id')->nullable();
            $table->text('admin_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_bookings');
    }
}
