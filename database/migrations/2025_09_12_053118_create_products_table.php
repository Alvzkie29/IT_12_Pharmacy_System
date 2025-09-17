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
        Schema::create('products', function (Blueprint $table) {
            $table->id('productID');
            $table->unsignedBigInteger('supplierID'); // must match suppliers PK type

            $table->foreign('supplierID')
                ->references('supplierID')
                ->on('suppliers')
                ->cascadeOnDelete();

            $table->string('productName');
            $table->decimal('price', 10, 2); 
            $table->enum('category', ['Antibiotic', 'Vitamins', 'Prescription', 'Analgesic']); 
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
