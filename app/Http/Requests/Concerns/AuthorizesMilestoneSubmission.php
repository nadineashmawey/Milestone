<?php

namespace App\Http\Requests\Concerns;

use App\Support\ZinaDemoData;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * BR-11 / SUB-1: only the current team leader of an Academic project submits, and only the active milestone.
 * Checked on the server, so a hidden button is never the only protection (BR-12).
 */
trait AuthorizesMilestoneSubmission
{
    public function authorize(): bool
    {
        $timeline = app(ZinaDemoData::class)->milestones((string) $this->route('project'));
        abort_if($timeline === null, 404);

        if ($timeline['project']['type'] === 'Independent') {
            throw new AuthorizationException('Independent projects don’t have official milestone submissions.');
        }

        $milestone = collect($timeline['milestones'])->firstWhere('code', strtoupper((string) $this->route('milestone')));
        abort_if($milestone === null, 404);

        if ($milestone['state'] !== 'active') {
            throw new AuthorizationException("{$milestone['code']} isn’t open for submissions.");
        }

        if (! $timeline['can_submit']) {
            throw new AuthorizationException("Only your team leader, {$timeline['leader']['name']}, can submit.");
        }

        return true;
    }
}
