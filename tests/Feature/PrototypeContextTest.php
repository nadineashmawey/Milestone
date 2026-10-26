<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Tests\TestCase;

class PrototypeContextTest extends TestCase
{
    public function test_switching_the_viewer_is_kept_in_the_session(): void
    {
        $this->post('/prototype/context', ['viewer' => 'malek', 'redirect' => '/help'])
            ->assertRedirect('/help')
            ->assertSessionHas(ZinaDemoData::SESSION_KEY.'.context.viewer', 'malek');
    }

    public function test_unknown_values_are_rejected(): void
    {
        $this->from('/help')
            ->post('/prototype/context', ['viewer' => 'someone-else', 'role' => 'superuser'])
            ->assertRedirect('/help')
            ->assertSessionHasErrors(['viewer', 'role']);
    }

    public function test_it_never_redirects_off_site(): void
    {
        $this->from('/help')
            ->post('/prototype/context', ['phase' => 'forming', 'redirect' => '//evil.example/steal'])
            ->assertRedirect('/help');
    }

    public function test_the_context_needs_a_csrf_token_outside_tests(): void
    {
        $this->assertContains('web', app('router')->getRoutes()->getByName('prototype.context')->gatherMiddleware());
    }
}
