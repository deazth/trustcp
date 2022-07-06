<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtilFloorSectionDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('util_floor_section_dailies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('building_id')->index();
            $table->unsignedBigInteger('floor_id')->index();
            $table->unsignedBigInteger('floor_section_id')->index();
            $table->date('report_date');
            $table->integer('total_seat');
            $table->integer('max_occupied_seat');
            $table->integer('free_seat');
            $table->decimal('utilization', 5, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('util_floor_section_dailies');
    }
}
