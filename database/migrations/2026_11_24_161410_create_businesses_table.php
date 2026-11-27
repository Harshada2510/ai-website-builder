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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 255);
            $table->string('business_slug', 255)->unique();
            $table->string('business_category', 100)->nullable();
            $table->string('city', 150);
            $table->string('address', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('google_business_url', 255)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index('business_name');
            $table->index('city');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
