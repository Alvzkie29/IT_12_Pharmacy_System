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
        // Add supplierID to stocks table
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('supplierID')->after('stockID');
            $table->foreign('supplierID')
                ->references('supplierID')
                ->on('suppliers')
                ->cascadeOnDelete();
        });

        // Remove supplierID from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplierID']);
            $table->dropColumn('supplierID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add supplierID to products table
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('supplierID');
            $table->foreign('supplierID')
                ->references('supplierID')
                ->on('suppliers')
                ->cascadeOnDelete();
        });

        // Remove supplierID from stocks table
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['supplierID']);
            $table->dropColumn('supplierID');
        });
    }
};
