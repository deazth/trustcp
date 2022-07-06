<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommonSkillsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_skillsets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('category', 1); // (p)redefined by admin or (m)anually added
            //$table->string('skillgroup');
            $table->string('name');
            //$table->string('skilltype');
            $table->integer('skill_category_id');
            $table->integer('skill_type_id');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_skillsets');
    }
}
