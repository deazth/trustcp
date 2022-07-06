<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNeoWsrHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('neo_wsr_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('persno');
            $table->date('input_date');
            $table->string('day_descr', 100)->nullable();
            $table->decimal('expected_hours', 5, 2)->nullable();
            $table->boolean('is_work_day')->nullable();
            $table->text('remark')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('neo_wsr_histories');
    }
}
