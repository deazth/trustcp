<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCekinsToFsDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('util_floor_section_dailies', function (Blueprint $table) {
          $table->unsignedInteger('min_duration')->default(0);
          $table->unsignedInteger('max_duration')->default(0);
          $table->unsignedInteger('avg_duration')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('util_floor_section_dailies', function (Blueprint $table) {
          $table->dropColumn('min_duration');
          $table->dropColumn('max_duration');
          $table->dropColumn('avg_duration');
        });
    }
}
