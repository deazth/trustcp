<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIcToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('employee_profile', function (Blueprint $table) {
        $table->id();
        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->integer('personel_no');
        $table->string('name', 150);
        $table->string('status', 50);
        $table->integer('group_no');
        $table->string('group_name', 150);
        $table->integer('unit_no');
        $table->string('unit_name', 150);
        $table->string('cost_center', 50);
        $table->integer('postion_no');
        $table->string('postion_name', 150);
        $table->string('staff_id', 25);
        $table->integer('team_no');
        $table->integer('reportingto_no');
        $table->integer('company_no');
        $table->string('company_name', 150);
        $table->string('office_location', 100);
        $table->string('state', 100);
        $table->string('postition_status', 50);
        $table->string('position_level', 100);
        $table->string('load_status', 1)->default('N')->index();
        $table->string('lob_id', 5);
        $table->string('lob_descr', 255);
        $table->string('band', 10);

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('employee_profile');
    }
}
