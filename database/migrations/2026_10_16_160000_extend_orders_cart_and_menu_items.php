<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('notes');
            $table->string('coupon_code')->nullable()->after('delivery_address');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('options_label')->nullable()->after('name');
            $table->json('option_ids')->nullable()->after('options_label');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('options')->nullable()->after('menu_item_id');
            $table->string('options_key')->default('')->after('options');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['menu_item_id']);
            $table->dropUnique(['user_id', 'menu_item_id']);
            $table->unique(['user_id', 'menu_item_id', 'options_key']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->cascadeOnDelete();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('cuisine')->nullable()->after('description');
            $table->boolean('is_veg')->default(false)->after('cuisine');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['cuisine', 'is_veg']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['menu_item_id']);
            $table->dropUnique(['user_id', 'menu_item_id', 'options_key']);
            $table->unique(['user_id', 'menu_item_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->cascadeOnDelete();
            $table->dropColumn(['options', 'options_key']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['options_label', 'option_ids']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_address', 'coupon_code', 'discount']);
        });
    }
};
