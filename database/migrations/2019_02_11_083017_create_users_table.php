<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');    
            $table->timestamps();        
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('staff_no', 10);
            $table->integer('role')->nullable();
            $table->integer('curr_reserve')->nullable();
            $table->integer('curr_checkin')->nullable();
            $table->integer('last_checkin')->nullable();
            $table->string('mobile_no', 15)->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->string('allowed_building', 255)->nullable();
            $table->string('lob', 20)->nullable();
            $table->integer('status')->nullable();
            $table->string('unit', 255)->nullable();
            $table->string('subunit', 255)->nullable();
            $table->string('jobtype')->nullable();
            $table->integer('isvendor')->default(0);
            
            $table->boolean('verified')->default(true);
            $table->integer('partner_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->string('pushnoti_id')->nullable();
            $table->integer('curr_attendance')->nullable();
            $table->integer('avatar_rank')->default('0');

            $table->integer('persno')->nullable();
            $table->integer('report_to')->nullable();
            $table->string('position', 500)->nullable();
            $table->string('cost_center', 10)->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('section_id')->nullable();
            $table->string('job_grade', 10)->default('1');
            $table->integer('last_location_id')->nullable();
            $table->string('new_ic')->nullable();
            $table->string('teamab', 50)->nullable();

            $table->index([ 'staff_no' ], 'usr_staffno');
            $table->index([ 'report_to' ], 'usr_reportto');


            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
