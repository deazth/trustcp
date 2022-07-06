<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGwdActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gwd_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('activity_type_id');
            $table->string('parent_number')->nullable();
            $table->string('title');
            $table->text('details')->nullable();
            $table->decimal('hours_spent', 4, 2);
            $table->integer('unit_id')->nullable();
            $table->integer('partner_id')->nullable();
            $table->date('activity_date');
            $table->integer('checkin_id')->nullable();
            $table->integer('task_category_id')->default(0);
            $table->boolean('isleave')->default(false);
            $table->string('leave_remark')->nullable();
            $table->integer('daily_performance_id')->nullable();
            $table->integer('tribe_id')->nullable();
            $table->integer('act_sub_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gwd_activities');
    }
}
