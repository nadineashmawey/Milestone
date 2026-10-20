<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'email', 'category', 'description', 'status', 'reference'])]
class ProblemReport extends Model
{
    /** Labels for these live in config/milestone_home.php under report_categories. */
    public const CATEGORIES = ['cant_sign_in', 'team_membership', 'submission_upload', 'grades_feedback', 'other'];

    public const STATUSES = ['open', 'in_progress', 'resolved'];

    protected $attributes = [
        'status' => 'open',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A short reference people can quote by email, like MS-4821.
     */
    public static function newReference(): string
    {
        do {
            $reference = 'MS-'.random_int(1000, 99999);
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
