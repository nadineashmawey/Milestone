<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentMilestonesTest extends TestCase
{
    private const PAGE = '/student/projects/software-engineering-capstone/milestones';

    private const DRAFT = self::PAGE.'/m2/draft';

    private const SUBMIT = self::PAGE.'/m2/submit';

    private function asMember(): static
    {
        return $this->withSession([ZinaDemoData::SESSION_KEY => ['context' => ['viewer' => 'malek']]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validDraft(array $overrides = []): array
    {
        return [
            'design_document' => UploadedFile::fake()->create('design-v2.pdf', 2048, 'application/pdf'),
            'presentation' => UploadedFile::fake()->create('design-v2.pptx', 4096),
            'repository' => 'https://github.com/team-1/capstone',
            'checklist' => ['final' => '1', 'access' => '1', 'approved' => '1'],
            ...$overrides,
        ];
    }

    public function test_it_shows_the_timeline_from_m1_to_m3(): void
    {
        $this->get(self::PAGE)
            ->assertOk()
            ->assertSeeInOrder(['M1 · Proposal', 'M2 · Design Document', 'M3 · Final Demo'])
            ->assertSee('Graded · locked')
            ->assertSee('41')
            ->assertSee('Problem framing')
            ->assertSee('Opens after Professor Lee reviews M2.')
            ->assertSee('Academic');
    }

    public function test_the_active_milestone_shows_its_deadline_status_and_feedback(): void
    {
        $this->get(self::PAGE)
            ->assertOk()
            ->assertSee('Sun, Oct 18 · 5:00 PM')
            ->assertSee('days left')
            ->assertSeeInOrder(ZinaDemoData::SUBMISSION_STATUSES)
            ->assertSee('Needs resubmission')
            ->assertSee('Sara Ahmed')
            ->assertSee('Teaching assistant')
            ->assertSee('Late · no penalty');
    }

    public function test_the_leader_gets_the_upload_form(): void
    {
        $this->get(self::PAGE)
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('Review submission')
            ->assertDontSee('Only your team leader');
    }

    public function test_a_team_member_sees_the_requirements_but_cannot_submit(): void
    {
        $this->asMember()
            ->get(self::PAGE)
            ->assertOk()
            ->assertSee('Only your team leader, Aya Hassan, can submit.')
            ->assertSee('Design document')
            ->assertDontSee('enctype="multipart/form-data"', false);
    }

    public function test_the_server_refuses_a_submission_from_a_team_member(): void
    {
        $this->asMember()->post(self::DRAFT, $this->validDraft())->assertForbidden();
        $this->asMember()->post(self::SUBMIT)->assertForbidden();
    }

    public function test_locked_milestones_and_independent_projects_refuse_submissions(): void
    {
        $this->post(self::PAGE.'/m3/draft', $this->validDraft())->assertForbidden();
        $this->post('/student/projects/campus-navigation-app/milestones/m2/draft', $this->validDraft())->assertForbidden();
        $this->post('/student/projects/no-such-project/milestones/m2/draft', $this->validDraft())->assertNotFound();
    }

    public function test_independent_projects_explain_there_are_no_submissions(): void
    {
        $this->get('/student/projects/campus-navigation-app/milestones')
            ->assertOk()
            ->assertSee('Independent projects don’t have milestone submissions.')
            ->assertSee('Independent');
    }

    public function test_every_deliverable_and_checklist_item_is_required(): void
    {
        $this->from(self::PAGE)
            ->post(self::DRAFT, [])
            ->assertRedirect(self::PAGE)
            ->assertSessionHasErrors(['design_document', 'presentation', 'repository', 'checklist']);

        $this->from(self::PAGE)
            ->post(self::DRAFT, $this->validDraft(['checklist' => ['final' => '1']]))
            ->assertSessionHasErrors(['checklist.access', 'checklist.approved']);
    }

    public function test_files_over_25_mb_are_rejected(): void
    {
        $this->from(self::PAGE)
            ->post(self::DRAFT, $this->validDraft(['design_document' => UploadedFile::fake()->create('huge.pdf', 26 * 1024)]))
            ->assertSessionHasErrors(['design_document' => 'Each file can be up to 25 MB. Compress it or split it into a ZIP.']);
    }

    public function test_unsupported_file_types_and_non_github_links_are_rejected(): void
    {
        $this->from(self::PAGE)
            ->post(self::DRAFT, $this->validDraft([
                'presentation' => UploadedFile::fake()->create('virus.exe', 10),
                'repository' => 'https://gitlab.com/team-1/capstone',
            ]))
            ->assertSessionHasErrors(['presentation', 'repository']);
    }

    public function test_a_valid_draft_goes_to_the_confirm_step(): void
    {
        $this->post(self::DRAFT, $this->validDraft())->assertRedirect(self::PAGE.'#confirm');

        $this->get(self::PAGE)
            ->assertOk()
            ->assertSee('Review version 2 before you submit')
            ->assertSee('design-v2.pdf')
            ->assertSee('Submit version 2 to Professor Lee');
    }

    public function test_a_failed_upload_keeps_the_draft_and_offers_a_retry(): void
    {
        $this->post(self::DRAFT, $this->validDraft());

        $this->post(self::SUBMIT, ['simulate_failure' => '1'])->assertRedirect(self::PAGE.'#confirm');

        $this->get(self::PAGE)
            ->assertSee('We couldn’t store version 2.')
            ->assertSee('Retry submission')
            ->assertSee('design-v2.pdf')
            ->assertDontSee('Version 2 received');
    }

    public function test_confirming_stores_a_new_version_and_shows_a_receipt(): void
    {
        $this->post(self::DRAFT, $this->validDraft());

        $this->post(self::SUBMIT)->assertRedirect(self::PAGE.'#receipt');

        $this->get(self::PAGE)
            ->assertOk()
            ->assertSee('Version 2 received')
            ->assertSee('Submitted by Aya Hassan')
            ->assertDontSee('Prototype: saved for this session')
            ->assertSee('2 versions, all kept');
    }

    public function test_submitting_without_a_draft_is_handled(): void
    {
        $this->post(self::SUBMIT)->assertRedirect(self::PAGE.'#submit');
        $this->get(self::PAGE)->assertSee('There was nothing to submit.');
    }

    public function test_each_state_renders(): void
    {
        $this->get(self::PAGE.'?state=empty')->assertOk()->assertSee('No milestones yet.');
        $this->get(self::PAGE.'?state=loading')->assertOk()->assertSee('Loading milestones');
        $this->get(self::PAGE.'?state=error')->assertOk()->assertSee('We couldn’t load the milestones.')->assertSee('Try again');
    }

    public function test_unknown_projects_are_not_found(): void
    {
        $this->get('/student/projects/no-such-project/milestones')->assertNotFound();
    }
}
