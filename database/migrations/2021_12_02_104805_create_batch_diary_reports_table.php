<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchDiaryReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_diary_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('class_name', 200);
            $table->unsignedBigInteger('obj_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status', 20)->default('Queued');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();
            $table->text('extra_info')->nullable();
            $table->string('filename', 250)->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_diary_reports');
    }
}
