<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id('batchID');
            $table->unsignedBigInteger('productID');
            $table->string('batchNo')->nullable();
            $table->date('expiryDate')->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->boolean('availability')->default(true);
            $table->timestamps();

            $table->foreign('productID')->references('productID')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
