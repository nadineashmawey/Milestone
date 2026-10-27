<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\Navigation;

class ProjectPolicy
{
    /** Only professors create and publish academic projects (UC-02). */
    public function create(User $user): bool
    {
        return Navigation::normalise((string) $user->role) === 'professor';
    }

    /** Exactly one professor owns an academic project. */
    public function view(User $user, Project $project): bool
    {
        return $this->create($user) && $project->professor_id === $user->id;
    }

    /** Drafts can be edited and published; published projects are locked here. */
    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project) && $project->isDraft();
    }
}
