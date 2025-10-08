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
        Schema::create('sales', function (Blueprint $table) {
            $table->id('saleID');
            $table->unsignedBigInteger('employeeID');
            $table->decimal('totalAmount', 10, 2);
            $table->decimal('cash_received', 10, 2);   // New column
            $table->decimal('change_given', 10, 2);    // New column
            $table->boolean('isDiscounted')->default(false);
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discountAmount', 10, 2)->default(0);
            $table->dateTime('saleDate');
            $table->timestamps();

            $table->foreign('employeeID')->references('employeeID')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
