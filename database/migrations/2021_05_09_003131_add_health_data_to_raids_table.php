<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHealthDataToRaidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raids', function (Blueprint $table) {
            $table->string('p1_health')->nullable()->default(null)->after('raid_name');
            $table->string('p2_health')->nullable()->default(null)->after('raid_name');
            $table->string('p3_health')->nullable()->default(null)->after('raid_name');
            $table->string('p4_health')->nullable()->default(null)->after('raid_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raids', function (Blueprint $table) {
            $table->dropColumn('p1_health');
            $table->dropColumn('p2_health');
            $table->dropColumn('p3_health');
            $table->dropColumn('p4_health');
        });
    }
}
