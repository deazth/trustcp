<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersSkillHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pers_skill_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('personal_skillset_id');
            $table->integer('action_user_id');
            $table->string('remark', 500)->nullable();
            $table->string('action', 100)->nullable();
            $table->text('extra_info')->nullable();
            $table->integer('newlevel')->default(0);
            $table->integer('oldlevel')->default(0);



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pers_skill_histories');
    }
}
