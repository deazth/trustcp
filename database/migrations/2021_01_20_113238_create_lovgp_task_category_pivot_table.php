<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLovgpTaskCategoryPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lovgp_task_category', function (Blueprint $table) {
            $table->integer('lovgp_id')->unsigned()->index();
            $table->foreign('lovgp_id')->references('id')->on('lovgps')->onDelete('cascade');
            $table->integer('task_category_id')->unsigned()->index();
            $table->foreign('task_category_id')->references('id')->on('task_categories')->onDelete('cascade');
            $table->primary(['lovgp_id', 'task_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lovgp_task_category');
    }
}
