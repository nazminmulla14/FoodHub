<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['restaurant_id', 'status', 'created_at'], 'orders_restaurant_status_created_index');
            $table->index(['user_id', 'created_at'], 'orders_user_created_index');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index(['restaurant_id', 'is_available', 'deleted_at'], 'menu_items_restaurant_available_index');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->index(['status', 'is_active'], 'restaurants_status_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_restaurant_status_created_index');
            $table->dropIndex('orders_user_created_index');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex('menu_items_restaurant_available_index');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex('restaurants_status_active_index');
        });
    }
};
