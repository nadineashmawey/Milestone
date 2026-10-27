<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * UC-02: the Create project wizard publishes a real project.
 */
class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $professor;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->professor = User::where('email', 'professor@demo.milestone.test')->first();
        $this->course = Course::where('code', 'CS 490')->first();
    }

    /**
     * What the wizard sends when every step is filled in (its field names are Nadine's).
     *
     * @return array<string, mixed>
     */
    private function valid(array $overrides = []): array
    {
        return array_replace([
            'intent' => 'publish',
            'submission_key' => 'key-'.uniqid(),
            'title' => 'Mobile Health Tracker',
            'color' => 'Violet',
            'course' => $this->course->id,
            'semester' => 'Fall 2026',
            'description' => 'Build a mobile app that helps students track sleep and study habits.',
            'goals' => ['Ship an accessible MVP', ''],
            'milestones' => [
                ['name' => 'Proposal', 'deadline' => '2026-10-11', 'deliverable' => 'PDF report', 'weight' => 20],
                ['name' => 'Design Document', 'deadline' => '2026-10-18', 'deliverable' => 'Mixed', 'weight' => 35],
                ['name' => 'Final Demo', 'deadline' => '2026-11-08', 'deliverable' => 'GitHub link', 'weight' => 45],
            ],
            'grade' => 50,
            'late_policy' => 'Allow with a Late flag',
            'criteria' => [['name' => 'Technical quality', 'points' => 30], ['name' => 'Presentation', 'points' => 20]],
            'resubmit' => '1',
            'team_min' => 2,
            'team_max' => 4,
            'formation' => 'Students choose',
            'team_deadline' => '2026-10-04',
            'leftover' => 'Professor decides',
            'permission' => 'Full review',
        ], $overrides);
    }

    private function publish(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->professor)->from(route('professor.projects.create'))->post(route('professor.projects.store'), $data);
    }

    public function test_the_wizard_posts_to_the_real_route_with_the_professors_courses(): void
    {
        $this->actingAs($this->professor)->get(route('professor.projects.create'))
            ->assertOk()
            ->assertViewIs('projects.create')
            ->assertSee('Create a supervised project')
            ->assertSee('Ready to publish')
            ->assertSee('action="'.route('professor.projects.store').'"', false)
            ->assertSee('name="submission_key"', false)
            ->assertSee('<option value="'.$this->course->id.'"', false)
            ->assertSee('CS 490 · Software Engineering')
            ->assertSee('Save as draft')
            ->assertDontSee('Backend publishing is not connected');
    }

    public function test_publishing_creates_one_active_project_with_its_milestones_rubric_and_an_audit_entry(): void
    {
        $before = Project::count();

        $response = $this->publish($this->valid());

        $project = Project::latest('id')->first();
        $response->assertRedirect(route('professor.projects.show', $project))->assertSessionHas('success', 'Project published.');
        $this->assertSame($before + 1, Project::count());
        $this->assertSame('active', $project->status);
        $this->assertNotNull($project->published_at);
        $this->assertSame($this->professor->id, $project->professor_id);
        $this->assertSame('manual', $project->leftover_mode);
        $this->assertSame(['Ship an accessible MVP'], $project->goals);
        $this->assertSame(['Proposal', 'Design Document', 'Final Demo'], $project->milestones->pluck('name')->all());
        $this->assertSame(['file', 'slides', 'images', 'link'], $project->milestones[1]->deliverable_types);
        $this->assertSame(['github'], $project->milestones[2]->deliverable_types);
        $this->assertSame(50, (int) $project->criteria()->sum('max_points'));
        $this->assertSame(1, AuditLog::where('action', 'project.published')->where('subject_id', $project->id)->count());

        $this->get(route('professor.projects.show', $project))->assertOk()->assertSee('Project published.')->assertSee('Mobile Health Tracker');
    }

    public function test_a_double_submit_creates_only_one_project(): void
    {
        $data = $this->valid(['submission_key' => 'same-page-load']);
        $before = Project::count();

        $first = $this->publish($data);
        $second = $this->publish($data);

        $this->assertSame($before + 1, Project::count());
        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidInput(): array
    {
        return [
            'no title' => [['title' => ''], 'title'],
            'no course' => [['course' => ''], 'course'],
            'someone else’s course' => [['course' => 999], 'course'],
            'no description' => [['description' => ''], 'description'],
            'grade of 0' => [['grade' => 0], 'grade'],
            'team minimum of 0' => [['team_min' => 0], 'team_min'],
            'maximum below minimum' => [['team_min' => 4, 'team_max' => 3], 'team_max'],
            'no milestones' => [['milestones' => []], 'milestones'],
            'milestone without a deliverable type' => [['milestones' => [['name' => 'Only', 'deadline' => '2026-10-11', 'deliverable_types' => [], 'weight' => 100]], 'criteria' => []], 'milestones.0.deliverable_types'],
            'dates out of order' => [['milestones' => [['name' => 'A', 'deadline' => '2026-10-18', 'deliverable' => 'PDF report', 'weight' => 50], ['name' => 'B', 'deadline' => '2026-10-11', 'deliverable' => 'PDF report', 'weight' => 50]]], 'milestones.1.deadline'],
            'milestone after the semester' => [['milestones' => [['name' => 'Late', 'deadline' => '2027-02-01', 'deliverable' => 'PDF report', 'weight' => 100]]], 'milestones.0.deadline'],
            'milestone before the formation deadline' => [['team_deadline' => '2026-10-15', 'milestones' => [['name' => 'Early', 'deadline' => '2026-10-11', 'deliverable' => 'PDF report', 'weight' => 100]]], 'milestones.0.deadline'],
            'weights not 100' => [['milestones' => [['name' => 'A', 'deadline' => '2026-10-11', 'deliverable' => 'PDF report', 'weight' => 40]]], 'milestones'],
            'rubric not equal to the grade' => [['criteria' => [['name' => 'Only', 'points' => 10]]], 'criteria'],
        ];
    }

    #[DataProvider('invalidInput')]
    public function test_each_validation_rule_blocks_publishing(array $overrides, string $field): void
    {
        $before = Project::count();

        $this->publish($this->valid($overrides))
            ->assertRedirect(route('professor.projects.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Project::count());
    }

    public function test_invalid_input_comes_back_on_the_right_step_with_everything_kept(): void
    {
        $data = $this->valid(['milestones' => [
            ['name' => 'Proposal', 'deadline' => '2026-10-18', 'deliverable' => 'PDF report', 'weight' => 50],
            ['name' => 'Kept milestone', 'deadline' => '2026-10-11', 'deliverable' => 'GitHub link', 'weight' => 50],
        ]]);

        $this->publish($data)->assertSessionHasErrors('milestones.1.deadline');

        // The redirect lands back on the wizard (Laravel ages the flashed errors only after that page).
        $this->followingRedirects()->actingAs($this->professor)->from(route('professor.projects.create'))->post(route('professor.projects.store'), $data)
            ->assertOk()
            ->assertSee('data-initial-step="1"', false)
            ->assertSee('value="Mobile Health Tracker"', false)
            ->assertSee('value="Kept milestone"', false)
            ->assertSee('<option selected>GitHub link</option>', false)
            ->assertSee('aria-invalid=true aria-describedby=milestones-1-deadline-error', false)
            ->assertSee('M2 must be due after M1.');
    }

    public function test_save_as_draft_needs_only_a_title_and_publishes_nothing(): void
    {
        $response = $this->publish(['intent' => 'draft', 'title' => 'Half-finished idea', 'submission_key' => 'draft-1']);

        $draft = Project::where('title', 'Half-finished idea')->first();
        $response->assertRedirect(route('professor.projects.show', $draft));
        $this->assertSame('draft', $draft->status);
        $this->assertNull($draft->published_at);
        $this->assertSame(0, AuditLog::where('subject_id', $draft->id)->count());

        $this->get(route('professor.projects.show', $draft))->assertSee('Draft saved.')->assertSee('Edit and publish');
    }

    public function test_a_draft_can_be_edited_and_published(): void
    {
        $this->publish(['intent' => 'draft', 'title' => 'Draft to finish', 'submission_key' => 'draft-2']);
        $draft = Project::where('title', 'Draft to finish')->first();

        $this->get(route('professor.projects.edit', $draft))->assertOk()->assertSee('value="Draft to finish"', false);
        $this->put(route('professor.projects.update', $draft), $this->valid(['title' => 'Draft to finish']))
            ->assertRedirect(route('professor.projects.show', $draft));

        $this->assertSame('active', $draft->fresh()->status);
        $this->assertSame(3, $draft->milestones()->count());
        $this->assertSame(1, AuditLog::where('action', 'project.published')->where('subject_id', $draft->id)->count());
        // Published projects are locked in the wizard.
        $this->get(route('professor.projects.edit', $draft))->assertForbidden();
    }

    public function test_cancel_saves_nothing(): void
    {
        $before = Project::count();

        $this->actingAs($this->professor)->get(route('professor.projects.create'))
            ->assertSee('href="'.route('professor.projects.index').'" class="ms-button ms-button--ghost" data-cancel', false);

        $this->assertSame($before, Project::count());
    }

    public function test_only_a_professor_can_publish(): void
    {
        $before = Project::count();
        $student = User::where('email', 'student@demo.milestone.test')->first();

        $this->actingAs($student)->post(route('professor.projects.store'), $this->valid())->assertForbidden();
        $this->actingAs($student)->get(route('professor.projects.create'))->assertForbidden();
        $this->assertSame($before, Project::count());
    }

    public function test_courses_lists_the_professors_courses_and_projects_from_the_database(): void
    {
        $this->publish($this->valid(['title' => 'Brand New Course Project']));

        $this->actingAs($this->professor)->get(route('professor.courses.index'))
            ->assertOk()
            ->assertSee(route('professor.projects.create'))
            ->assertSee('Software Engineering Capstone')
            ->assertSee('Brand New Course Project')
            ->assertSee('8 teams')
            ->assertSee('3 milestones');

        $this->actingAs($this->professor)->get(route('professor.courses.index', ['course' => 'cs342']))->assertSee('Database Systems');
    }

    public function test_the_milestone_deliverable_types_are_the_documented_set(): void
    {
        $this->assertSame(['file', 'slides', 'images', 'link', 'github'], array_keys(Milestone::DELIVERABLE_TYPES));
    }
}
