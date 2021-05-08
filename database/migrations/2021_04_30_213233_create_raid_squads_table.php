<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRaidSquadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raid_squads', function (Blueprint $table) {
            $table->integer('sw_guild_squads_id')->unsigned();
            $table->integer('raid_name_id')->unsigned();
            $table->integer('phase')->unsigned();
            $table->integer('damage')->unsigned();
            $table->timestamps();
            $table->index('sw_guild_squads_id');
            $table->index('raid_name_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raid_squads');
    }
}
