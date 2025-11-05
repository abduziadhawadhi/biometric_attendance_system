<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttendanceDateToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('attendance_date')->nullable()->after('employee_id');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('attendance_date');
        });
    }
}

