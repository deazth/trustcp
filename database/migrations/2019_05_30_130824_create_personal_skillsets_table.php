<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalSkillsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_skillsets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('common_skill_id');
            $table->integer('staff_id');
            $table->integer('level')->default(0);
            $table->string('status', 2)->default('N');
            $table->integer('prev_level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_skillsets');
    }
}
