<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_number', 32)->unique();
            $table->string('public_token', 64)->unique();
            $table->foreignId('umrah_package_id')->constrained()->restrictOnDelete();
            $table->foreignId('schedule_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('whatsapp', 32);
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('pilgrims_count');
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('quota_deducted_at')->nullable();
            $table->timestamp('quota_restored_at')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
