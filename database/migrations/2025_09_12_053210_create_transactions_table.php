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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transactionID');

            $table->unsignedBigInteger('saleID');
            $table->unsignedBigInteger('stockID');

            $table->foreign('saleID')
                ->references('saleID')
                ->on('sales')
                ->cascadeOnDelete();

            $table->foreign('stockID')
                ->references('stockID')
                ->on('stocks')
                ->cascadeOnDelete();

            $table->integer('quantity');
            $table->decimal('unitPrice', 10, 2);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
