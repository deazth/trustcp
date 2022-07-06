<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBauExpTypeIdToInvolvements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('involvements', function (Blueprint $table) {
          $table->unsignedInteger('bau_exp_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('involvements', function (Blueprint $table) {
          $table->dropColumn('bau_exp_type_id');
        });
    }
}
