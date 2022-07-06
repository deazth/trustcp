<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_infos', function (Blueprint $table) {
          $table->foreignId('cur_job_type_id')
          ->nullable()
          ->constrained('pers_job_types')
          ->nullOnDelete();
          $table->foreignId('pref_job_type_id')
          ->nullable()
          ->constrained('pers_job_types')
          ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_infos', function (Blueprint $table) {
          $table->dropColumn('cur_job_type_id');
          $table->dropColumn('pref_job_type_id');
        });
    }
}
