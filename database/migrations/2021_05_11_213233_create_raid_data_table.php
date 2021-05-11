<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRaidDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raid_data', function (Blueprint $table) {
            $table->integer('sw_guild_squads_id')->unsigned();
            $table->integer('raid_name_id')->unsigned();
            $table->integer('phase')->unsigned();
            $table->integer('damage')->unsigned();
            $table->string('p1');
            $table->string('p2');
            $table->string('p3');
            $table->string('p4');
            $table->string('p5');
            $table->integer('p1_gp')->unsigned();
            $table->integer('p2_gp')->unsigned();
            $table->integer('p3_gp')->unsigned();
            $table->integer('p4_gp')->unsigned();
            $table->integer('p5_gp')->unsigned();
            $table->integer('total_gp')->unsigned();
            $table->timestamps();
            $table->index('sw_guild_squads_id');
            $table->index('raid_name_id');
            $table->index('phase');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raid_data');
    }
}
