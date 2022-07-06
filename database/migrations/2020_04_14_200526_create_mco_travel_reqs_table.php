<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMcoTravelReqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_information', function (Blueprint $table) {
          $table->id();
          $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
          $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
          $table->integer('personel_no');
          $table->string('leave_code', 20);
          $table->string('leave_describtion', 250);
          $table->timestamp('date_start')->nullable();
          $table->timestamp('date_end')->nullable();
          $table->string('status', 40);
          $table->string('load_status', 1)->default('N')->index();
          $table->string('timestamp', 45);
          $table->string('operation', 45);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_information');
    }
}
