<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'professor_id', 'owner_id', 'type', 'title', 'description', 'goals', 'total_grade', 'team_min', 'team_max',
    'formation_deadline', 'leftover_mode', 'status', 'settings', 'submission_key', 'published_at',
])]
class Project extends Model
{
    public const STATUSES = ['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'];

    protected $attributes = ['type' => 'academic', 'status' => 'draft', 'leftover_mode' => 'auto'];

    protected function casts(): array
    {
        return [
            'goals' => 'array',
            'settings' => 'array',
            'formation_deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<User, $this> */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /** @return HasMany<Milestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('position');
    }

    /** @return HasMany<RubricCriterion, $this> */
    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('position');
    }

    /** @return HasMany<Team, $this> */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * The student who created an independent project. Academic projects have a professor instead.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @param Builder<Project> $query */
    public function scopeOwnedBy(Builder $query, User $professor): void
    {
        $query->where('professor_id', $professor->id);
    }

    /**
     * A student's own independent projects.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeIndependentFor(Builder $query, User $student): void
    {
        $query->where('type', 'independent')->where('owner_id', $student->id);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** The first milestone due after $now, if any. Needs milestones loaded. */
    public function nextMilestone(CarbonInterface $now): ?Milestone
    {
        return $this->milestones->filter(fn (Milestone $milestone) => $milestone->due_at?->greaterThan($now))->sortBy('due_at')->first();
    }

    /**
     * Share of the milestone weight whose deadline has passed. A schedule measure until submissions are stored.
     */
    public function progress(CarbonInterface $now): int
    {
        $total = $this->milestones->sum('weight');

        return $total ? (int) round($this->milestones->filter(fn (Milestone $milestone) => $milestone->due_at?->lessThanOrEqualTo($now))->sum('weight') / $total * 100) : 0;
    }
}
