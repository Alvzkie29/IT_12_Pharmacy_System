<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('isDiscounted')->default(false)->after('totalAmount');
            $table->decimal('subtotal', 10, 2)->nullable()->after('isDiscounted');
            $table->decimal('discountAmount', 10, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['isDiscounted', 'subtotal', 'discountAmount']);
        });
    }
};