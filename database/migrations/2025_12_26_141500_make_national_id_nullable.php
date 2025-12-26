<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal for this simple nullability change.
        DB::statement("ALTER TABLE `users` MODIFY `national_id` VARCHAR(10) NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to NOT NULL. Be careful: this will fail if NULL values exist.
        DB::statement("ALTER TABLE `users` MODIFY `national_id` VARCHAR(10) NOT NULL;");
    }
};
