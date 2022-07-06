<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPercToInvolvementJobscopeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('involvement_jobscope', function (Blueprint $table) {
          $table->decimal('perc', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('involvement_jobscope', function (Blueprint $table) {
          $table->dropColumn('perc');
        });
    }
}
