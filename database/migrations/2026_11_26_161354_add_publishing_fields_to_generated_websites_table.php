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
        Schema::table('generated_websites', function (Blueprint $table) {
            if (! Schema::hasColumn('generated_websites', 'slug')) {
                $table->string('slug', 255)->nullable()->unique()->after('site_title');
            }
            if (! Schema::hasColumn('generated_websites', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('generation_status');
            }
            if (! Schema::hasColumn('generated_websites', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_websites', function (Blueprint $table) {
            if (Schema::hasColumn('generated_websites', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('generated_websites', 'is_published')) {
                $table->dropColumn('is_published');
            }
            if (Schema::hasColumn('generated_websites', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
