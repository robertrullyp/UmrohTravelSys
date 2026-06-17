<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->boolean('is_primary')->default(false)->after('is_active');
        });

        $primaryId = DB::table('contacts')
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('id') ?? DB::table('contacts')->orderBy('id')->value('id');

        if ($primaryId) {
            DB::table('contacts')
                ->where('id', $primaryId)
                ->update(['is_primary' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropColumn('is_primary');
        });
    }
};
