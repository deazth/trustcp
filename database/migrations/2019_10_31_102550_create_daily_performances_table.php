<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_performances', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->index();
            $table->date('record_date')->nullable()->index();
            $table->decimal('expected_hours', 5, 2)->default(8.0);
            $table->decimal('actual_hours', 5, 2)->default(0.0);
            $table->boolean('is_off_day')->default(false);
            $table->integer('leave_type_id')->nullable();
            $table->integer('unit_id');
            $table->boolean('zerorized')->default(false);
            $table->datetime('start_work')->nullable();
            $table->string('remark', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_performances');
    }
}
