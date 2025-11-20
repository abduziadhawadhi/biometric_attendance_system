<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLateOvertimeToAttendances extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('late_minutes')->nullable()->after('status');
            $table->integer('overtime_minutes')->nullable()->after('late_minutes');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_minutes','overtime_minutes']);
        });
    }
}
