<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraToAreaBookEqReqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('area_booking_equipment_type', function (Blueprint $table) {
          $table->smallInteger('count')->default(1);
          $table->string('status', 100)->default('New');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('area_booking_equipment_type', function (Blueprint $table) {
          $table->dropColumn('count');
          $table->dropColumn('status');
        });
    }
}
