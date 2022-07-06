<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoordMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coord_mappings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->decimal('latitude', 8,5)->nullable();
            $table->decimal('longitude', 8,5)->nullable();
            $table->string('address', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coord_mappings');
    }
}
