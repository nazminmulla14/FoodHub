<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone', 30)->nullable();
            $table->unsignedTinyInteger('party_size');
            $table->dateTime('booked_at');
            $table->string('status', 30)->default('pending');
            $table->text('special_request')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'booked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_bookings');
    }
};
