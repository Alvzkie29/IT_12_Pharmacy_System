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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id('stockID');
            $table->unsignedBigInteger('productID');
            $table->unsignedBigInteger('employeeID');
            $table->foreign('productID')->references('productID')->on('products')->cascadeOnDelete();
            $table->foreign('employeeID')->references('employeeID')->on('employees')->cascadeOnDelete();
            $table->enum('type', ['IN', 'OUT']);
            $table->string('reason')->nullable(); // For stock movement details
            $table->decimal('purchase_price', 10, 2); // Price bought from supplier
            $table->decimal('selling_price', 10, 2); // Price sold to customers
            $table->integer('quantity');
            $table->boolean('availability')->default(true);
            $table->string('batchNo')->nullable();
            $table->date('expiryDate')->nullable();
            $table->date('movementDate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
