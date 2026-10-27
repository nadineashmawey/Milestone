<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Courses, academic projects and their milestones, rubric and teams (SRS §8.1, PRJ-4–7, PROF-2, PROF-4),
 * plus a simple audit log for publishing. Every foreign key is indexed (NFR-18).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 120);
            $table->string('semester', 40);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('schedule', 120)->nullable();
            $table->string('tone', 20)->default('blue');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('professor_id')->index()->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['academic', 'independent'])->default('academic');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->json('goals')->nullable();
            $table->unsignedSmallInteger('total_grade')->nullable();
            $table->unsignedTinyInteger('team_min')->nullable();
            $table->unsignedTinyInteger('team_max')->nullable();
            $table->date('formation_deadline')->nullable();
            $table->enum('leftover_mode', ['auto', 'manual'])->default('auto');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->index();
            // Wizard choices that have no column of their own yet: colour, formation method, late policy, TA permission…
            $table->json('settings')->nullable();
            // One key per wizard page load, so a double-click or resend can't create two projects.
            $table->string('submission_key', 64)->nullable()->unique();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->index()->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->json('deliverable_types')->nullable();
            $table->unsignedTinyInteger('weight')->nullable();
            $table->unsignedSmallInteger('points')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->index()->constrained()->cascadeOnDelete();
            // The wizard grades the whole project; a criterion can be tied to one milestone later.
            $table->foreignId('milestone_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('max_points');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->index()->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->nullableMorphs('subject');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('courses');
    }
};
