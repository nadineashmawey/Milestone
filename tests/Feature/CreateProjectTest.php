<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    public function test_create_project_form_is_available_with_all_setup_sections(): void
    {
        $this->get(route('professor.projects.create'))
            ->assertOk()
            ->assertViewIs('projects.create')
            ->assertSee('Create a supervised project')
            ->assertSee('Team formation')
            ->assertSee('Ready to publish')
            ->assertSee('name="milestones[0][name]"', false)
            ->assertSee('name="criteria[0][points]"', false);
    }

    public function test_courses_links_to_create_project(): void
    {
        $this->get(route('professor.courses.index'))
            ->assertOk()
            ->assertSee(route('professor.projects.create'));
    }
}
