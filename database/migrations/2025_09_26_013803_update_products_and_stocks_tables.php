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
        Schema::table('stocks', function (Blueprint $table) {
            // Rename old ambiguous "price" column into purchase_price
            $table->renameColumn('price', 'purchase_price');

            // Add selling_price column for each stock batch
            $table->decimal('selling_price', 10, 2)->after('purchase_price');
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price'); // remove product-level price
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('selling_price');
            $table->renameColumn('purchase_price', 'price');
        });
    }
};
