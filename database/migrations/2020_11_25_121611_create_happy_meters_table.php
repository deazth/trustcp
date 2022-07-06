<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHappyMetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('happy_meters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('staff_no');
            $table->integer('type_id');
            $table->string('type_desc')->nullable();
            $table->integer('reason_id')->nullable();
            $table->string('reason_desc')->nullable();
            $table->string('remark',500)->nullable();
            $table->string('sourcefromtrust')->nullable();
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
        Schema::dropIfExists('happy_meters');
    }
}
