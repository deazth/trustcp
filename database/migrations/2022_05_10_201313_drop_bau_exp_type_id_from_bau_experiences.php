<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBauExpTypeIdFromBauExperiences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bau_experiences', function (Blueprint $table) {
          $table->dropColumn('bau_exp_type_id');
          $table->dropColumn('added_by');
          $table->dropColumn('edited_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bau_experiences', function (Blueprint $table) {
            //
        });
    }
}
