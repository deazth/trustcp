<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('seat_id');
            $table->string('ticket_id', 250);
            $table->string('status', 10);
            $table->unsignedInteger('resolve_id')->nullable();
            $table->string('app', 250);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixits');
    }
}
