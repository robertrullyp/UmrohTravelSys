<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->unsignedSmallInteger('capacity')->default(0)->after('quota');
        });

        DB::table('schedules')->update([
            'capacity' => DB::raw('quota'),
        ]);
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropColumn('capacity');
        });
    }
};
