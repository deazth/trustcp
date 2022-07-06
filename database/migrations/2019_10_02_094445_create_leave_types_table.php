<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('code', 10);
            $table->string('descr')->nullable();
            $table->string('category', 50)->default('Unplanned');
            $table->boolean('ishalfday')->default(false);
            $table->float('hours_value', 5, 2)->default(0.0);
            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
}
