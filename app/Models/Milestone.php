<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'name', 'description', 'due_at', 'deliverable_types', 'weight', 'points', 'position'])]
class Milestone extends Model
{
    /** Deliverable types a milestone can accept. */
    public const DELIVERABLE_TYPES = ['file' => 'File', 'slides' => 'Slides', 'images' => 'Images', 'link' => 'Link', 'github' => 'GitHub'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'deliverable_types' => 'array'];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function deliverableLabels(): string
    {
        return collect($this->deliverable_types ?? [])->map(fn (string $type) => self::DELIVERABLE_TYPES[$type] ?? $type)->implode(', ');
    }
}
