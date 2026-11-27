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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_theme_preset', 50)->nullable()->default('indigo');
            $table->string('default_primary_color', 7)->default('#4F46E5');
            $table->string('default_secondary_color', 7)->default('#0EA5E9');
            $table->string('default_background_color', 7)->default('#FFFFFF');
            $table->string('default_text_color', 7)->default('#1F2937');
            $table->string('generation_tone', 30)->default('professional');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
