<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->decimal('in_latitude', 10,7);
            $table->decimal('in_longitude', 10,7);
            $table->timestamp('clockin_time')->nullable();
            $table->decimal('out_latitude', 10,7)->nullable();
            $table->decimal('out_longitude', 10,7)->nullable();
            $table->timestamp('clockout_time')->nullable();
            $table->string('out_reason')->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('isvendor')->default(0);
            $table->boolean('overnight')->default(false);
            $table->timestamp('overnight_time')->nullable();
            $table->integer('minute_work')->default(0);
            $table->integer('minute_work_overnight')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
