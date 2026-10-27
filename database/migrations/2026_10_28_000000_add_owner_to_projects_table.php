<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Independent projects (PRJ-1) are owned by the student who created them and have no professor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('professor_id')->index()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('professor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
