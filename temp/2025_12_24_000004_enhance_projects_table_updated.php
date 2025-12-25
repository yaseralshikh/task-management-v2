<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: 2025_12_24_000004_enhance_projects_table
 * 
 * النسخة المحدثة - بدون budget و currency
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('color', 7)->default('#10B981')->after('slug');
            // تم حذف: budget و currency
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('end_date');
            $table->boolean('is_archived')->default(false)->after('status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete()->after('archived_at');
            
            $table->index('is_archived');
            $table->index(['status', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn([
                'slug', 'color', 'progress_percentage',
                'is_archived', 'archived_at', 'archived_by'
            ]);
        });
    }
};
