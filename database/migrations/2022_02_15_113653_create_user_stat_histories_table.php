<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_stat_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('rec_month')->index();
            $table->integer('active_user');
            $table->integer('diary_user');
            $table->integer('location_user');
            $table->integer('workspace_user');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_stat_histories');
    }
}
