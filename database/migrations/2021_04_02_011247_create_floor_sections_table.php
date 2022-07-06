<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFloorSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floor_sections', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('label', 250);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('floor_id');
            $table->integer('tracked_seat_count')->default(0);
            $table->integer('tracked_seat_occupied')->default(0);
            $table->boolean('status'); 

            $table->foreign('floor_id', 'fs_floor')->references('id')->on('floors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('floor_sections');
    }
}
