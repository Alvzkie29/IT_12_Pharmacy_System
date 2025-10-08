<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id('stockID');
            $table->unsignedBigInteger('batchID');
            $table->unsignedBigInteger('employeeID');
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->date('movementDate');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('batchID')->references('batchID')->on('product_batches')->cascadeOnDelete();
            $table->foreign('employeeID')->references('employeeID')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
