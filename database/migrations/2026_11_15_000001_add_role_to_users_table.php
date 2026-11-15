<?php
// Author: Lai Chee Kit
// TEMPORARY stand-in: the users table belongs to Module 1.
// Remove this file when Module 1's real users table is merged.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // student, staff or admin
            $table->string('role', 20)->default('student');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
