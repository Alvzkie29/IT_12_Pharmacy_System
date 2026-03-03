<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('reportID');

            $table->string('report_type'); 
            // daily_sales, monthly_sales, inventory, etc.

            $table->date('report_date')->nullable();

            $table->unsignedBigInteger('generated_by')->nullable();

            $table->foreign('generated_by')
                ->references('employeeID')
                ->on('employees')
                ->nullOnDelete();

            $table->string('s3_path'); // File location in S3
            $table->string('file_name')->nullable();

            $table->timestamp('generated_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
