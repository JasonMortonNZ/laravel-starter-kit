<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;

test('the invitation notification has an array representation', function (): void {
    $team = Team::factory()->create(['name' => 'Laravel Team']);
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'role' => TeamRole::Admin,
    ]);

    $notification = new TeamInvitationNotification($invitation);

    expect($notification->toArray($invitation))->toBe([
        'invitation_id' => $invitation->id,
        'team_id' => $team->id,
        'team_name' => 'Laravel Team',
        'role' => TeamRole::Admin->value,
    ]);
});
