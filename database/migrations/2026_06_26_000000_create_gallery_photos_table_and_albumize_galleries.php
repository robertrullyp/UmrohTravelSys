<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['gallery_id', 'sort_order']);
        });

        if (Schema::hasColumn('galleries', 'image_path')) {
            Schema::table('galleries', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->change();
            });

            DB::table('galleries')
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->orderBy('id')
                ->get(['id', 'image_path', 'created_at', 'updated_at'])
                ->each(function (object $gallery): void {
                    DB::table('gallery_photos')->insert([
                        'gallery_id' => $gallery->id,
                        'image_path' => $gallery->image_path,
                        'caption' => null,
                        'sort_order' => 0,
                        'created_at' => $gallery->created_at ?? now(),
                        'updated_at' => $gallery->updated_at ?? now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gallery_photos') && Schema::hasColumn('galleries', 'image_path')) {
            DB::table('galleries')
                ->orderBy('id')
                ->get(['id'])
                ->each(function (object $gallery): void {
                    $coverPath = DB::table('gallery_photos')
                        ->where('gallery_id', $gallery->id)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->value('image_path');

                    if (filled($coverPath)) {
                        DB::table('galleries')
                            ->where('id', $gallery->id)
                            ->update(['image_path' => $coverPath]);
                    }
                });
        }

        Schema::dropIfExists('gallery_photos');

        if (Schema::hasColumn('galleries', 'image_path')) {
            DB::table('galleries')
                ->whereNull('image_path')
                ->update(['image_path' => '']);

            Schema::table('galleries', function (Blueprint $table): void {
                $table->string('image_path')->nullable(false)->change();
            });
        }
    }
};
