<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwMetaReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_meta_report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('gas')->nullable();
            $table->integer('relic_squad_count')->nullable();
            $table->integer('gl_count')->nullable();
            $table->string('gl_list')->nullable();
            $table->integer('daily_tickets')->nullable();
            $table->integer('average_tickets')->nullable();
            $table->integer('weeks_below_tickets')->nullable();
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
        Schema::dropIfExists('sw_meta_report');
    }
}
