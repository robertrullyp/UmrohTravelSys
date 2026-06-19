<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah_packages', function (Blueprint $table): void {
            $table->string('seo_title', 70)->nullable()->after('description');
            $table->string('seo_description', 170)->nullable()->after('seo_title');
            $table->string('seo_image_path')->nullable()->after('seo_description');
            $table->boolean('is_indexable')->default(true)->after('is_active');
            $table->index(['is_active', 'is_indexable'], 'umrah_packages_search_index');
        });
    }

    public function down(): void
    {
        Schema::table('umrah_packages', function (Blueprint $table): void {
            $table->dropIndex('umrah_packages_search_index');
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'seo_image_path',
                'is_indexable',
            ]);
        });
    }
};
