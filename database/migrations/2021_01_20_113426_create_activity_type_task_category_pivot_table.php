<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityTypeTaskCategoryPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_type_task_category', function (Blueprint $table) {
            $table->integer('activity_type_id')->unsigned()->index();
            $table->foreign('activity_type_id', 'attc_atid')->references('id')->on('activity_types')->onDelete('cascade');
            $table->integer('task_category_id')->unsigned()->index();
            $table->foreign('task_category_id', 'attc_tcid')->references('id')->on('task_categories')->onDelete('cascade');
            $table->primary(['activity_type_id', 'task_category_id'], 'attc_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_type_task_category');
    }
}
