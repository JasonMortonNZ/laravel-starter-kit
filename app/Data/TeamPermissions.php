<?php

declare(strict_types=1);

namespace App\Data;

use JsonSerializable;

final readonly class TeamPermissions implements JsonSerializable
{
    public function __construct(
        public bool $canUpdateTeam,
        public bool $canDeleteTeam,
        public bool $canAddMember,
        public bool $canUpdateMember,
        public bool $canRemoveMember,
        public bool $canCreateInvitation,
        public bool $canCancelInvitation,
    ) {
        //
    }

    /**
     * Get the API representation of the permissions.
     *
     * @return array<string, bool>
     */
    public function jsonSerialize(): array
    {
        return [
            'can_update_team' => $this->canUpdateTeam,
            'can_delete_team' => $this->canDeleteTeam,
            'can_add_member' => $this->canAddMember,
            'can_update_member' => $this->canUpdateMember,
            'can_remove_member' => $this->canRemoveMember,
            'can_create_invitation' => $this->canCreateInvitation,
            'can_cancel_invitation' => $this->canCancelInvitation,
        ];
    }
}
