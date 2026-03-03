<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id('receiptID');

            $table->unsignedBigInteger('saleID');

            $table->foreign('saleID')
                ->references('saleID')
                ->on('sales')
                ->cascadeOnDelete();

            $table->string('s3_path'); // Where the PDF is stored in S3
            $table->string('file_name')->nullable();
            $table->timestamp('generated_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
