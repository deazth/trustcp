<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyUserStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_user_stats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('record_date')->index();
            $table->string('lob_descr', 250)->index();
            $table->integer('user_count')->default(0);
            $table->integer('location_count')->default(0);
            $table->integer('workspace_count')->default(0);
            $table->integer('diary_count')->default(0);
            $table->integer('user_in_group')->default(0);
            $table->integer('unique_count')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_user_stats');
    }
}
