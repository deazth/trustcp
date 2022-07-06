<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('floor_section_id');
            $table->string('label', 100);
            $table->string('seat_type', 100)->default('Seat');
            $table->string('priviledge', 100)->nullable();

            $table->string('qr_code', 255);
            $table->string('remark', 255)->nullable();
            $table->boolean('status');
            $table->integer('seat_capacity')->default(1);
            $table->integer('seat_utilized')->default(0);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('floor_section_id', 'seat_fs')->references('id')->on('floor_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seats');
    }
}
