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
            $table->id('stockID'); // UNSIGNED BIGINT PRIMARY KEY

            $table->unsignedBigInteger('productID');
            $table->unsignedBigInteger('employeeID');

            $table->foreign('productID')
                ->references('productID')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('employeeID')
                ->references('employeeID')
                ->on('employees')
                ->cascadeOnDelete();

            $table->enum('type', ['IN', 'OUT']);
            $table->decimal('price', 10, 2); 
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
