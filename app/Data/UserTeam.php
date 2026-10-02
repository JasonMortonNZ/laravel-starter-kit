<?php

declare(strict_types=1);

namespace App\Data;

use JsonSerializable;

final readonly class UserTeam implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public bool $isPersonal,
        public ?string $role,
        public ?string $roleLabel,
        public ?bool $isCurrent = null,
    ) {
        //
    }

    /**
     * Get the API representation of the team.
     *
     * @return array{id: int, name: string, slug: string, is_personal: bool, role: string|null, role_label: string|null, is_current: bool|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_personal' => $this->isPersonal,
            'role' => $this->role,
            'role_label' => $this->roleLabel,
            'is_current' => $this->isCurrent,
        ];
    }
}
