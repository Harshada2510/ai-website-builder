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
        Schema::create('business_source_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('business_file_id')->nullable()->constrained('business_files')->nullOnDelete();
            $table->string('source_type', 20);
            $table->string('source_url', 500)->nullable();
            $table->string('source_title', 255)->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('extraction_status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'source_type']);
            $table->index('extraction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_source_contents');
    }
};
