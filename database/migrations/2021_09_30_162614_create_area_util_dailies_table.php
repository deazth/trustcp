<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaUtilDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_util_dailies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('building_id')->index();
            $table->unsignedBigInteger('floor_id')->index();
            $table->unsignedBigInteger('seat_id')->index();
            $table->date('report_date');
            $table->smallInteger('total_hour_used');
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
        Schema::dropIfExists('area_util_dailies');
    }
}
