<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Team;
use App\Models\TeamInvitation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class UniqueTeamInvitation implements ValidationRule
{
    public function __construct(private Team $team)
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $isMember = $this->team->members()
            ->where('users.email', mb_strtolower($value))
            ->exists();

        if ($isMember) {
            $fail(__('This user is already a member of the team.'));

            return;
        }

        $hasPendingInvitation = TeamInvitation::query()
            ->pending()
            ->where('team_id', $this->team->id)
            ->where('email', mb_strtolower($value))
            ->exists();

        if ($hasPendingInvitation) {
            $fail(__('An invitation has already been sent to this email address.'));
        }
    }
}
