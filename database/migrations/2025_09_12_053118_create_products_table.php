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
            $table->unsignedBigInteger('supplierID'); // FK to suppliers

            $table->foreign('supplierID')
                ->references('supplierID')
                ->on('suppliers')
                ->cascadeOnDelete();

            $table->string('productName');
            $table->string('genericName'); // generic name of the medicine
            $table->string('productWeight'); // weight/dosage e.g. 500mg, 10ml

            $table->enum('dosageForm', [
                'Tablet', 
                'Capsule', 
                'Syrup', 
                'Injection', 
                'Cream', 
                'Ointment', 
                'Drops'
            ])->default('Tablet'); // type of medicine

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
