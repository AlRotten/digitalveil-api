<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usage', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->dateTime('day');
            $table->Integer('use_time');
            $table->String('location');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('application_id');
    	    $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('application_id')->references('id')->on('application');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usage');
    }
}
