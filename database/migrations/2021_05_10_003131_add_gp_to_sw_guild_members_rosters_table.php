<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGpToSwGuildMembersRostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_guild_members_rosters', function (Blueprint $table) {
            $table->integer('gp')->unsigned()->after('tier');
            $table->text('skills')->after('tier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_guild_members_rosters', function (Blueprint $table) {
            $table->dropColumn('gp');
            $table->dropColumn('skills');
        });
    }
}
