<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeIdToSwGuildSquadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_guild_squads', function (Blueprint $table) {
            $table->integer('sw_squad_types_id')->nullable()->default('1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_guild_squads', function (Blueprint $table) {
            $table->dropColumn('sw_squad_types_id');
        });
    }
}
