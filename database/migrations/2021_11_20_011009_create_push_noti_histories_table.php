<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushNotiHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_noti_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('user_id')->index();
            $table->string('sender', 250);
            $table->string('trigger_event', 250);
            $table->date('sent_date')->index();
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->string('status', 20);
            $table->text('resp_data')->nullable();
            $table->string('pushnoti_id', 100)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_noti_histories');
    }
}
