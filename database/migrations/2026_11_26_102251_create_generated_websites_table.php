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
        Schema::create('generated_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('site_title', 255)->nullable();
            $table->string('tagline', 500)->nullable();
            $table->json('generated_content')->nullable();
            $table->string('generation_status', 30)->default('pending');
            $table->string('ai_model', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'generation_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_websites');
    }
};
