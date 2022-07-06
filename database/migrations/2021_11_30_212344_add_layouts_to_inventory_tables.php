<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLayoutsToInventoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('floors', function (Blueprint $table) {
          $table->string('layout_file', 250)->nullable();
        });

        Schema::table('floor_sections', function (Blueprint $table) {
          $table->string('layout_file', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('floors', function (Blueprint $table) {
          $table->dropColumn('layout_file');
        });

        Schema::table('floor_sections', function (Blueprint $table) {
          $table->dropColumn('layout_file');
        });
    }
}
