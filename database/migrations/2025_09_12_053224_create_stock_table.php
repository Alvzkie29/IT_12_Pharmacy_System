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
        Schema::create('stock', function (Blueprint $table) {
            $table->id('stockID');
            $table->unsignedBigInteger('productID');
            $table->unsignedBigInteger('transactionID')->nullable();
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->boolean('isAvailable')->default(true);
            $table->string('batchNo')->nullable();
            $table->date('expiryDate')->nullable();
            $table->date('movementDate');
            $table->timestamps();

            $table->foreign('productID')
                ->references('productID')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('transactionID')
                ->references('transactionID')
                ->on('transactions')
                ->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
