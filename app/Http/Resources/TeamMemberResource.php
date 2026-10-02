<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, email: string, avatar: string|null, role: string, role_label: string}
     */
    public function toArray(Request $request): array
    {
        /** @var Membership $membership */
        $membership = $this->getRelation('pivot');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => null,
            'role' => $membership->role->value,
            'role_label' => $membership->role->label(),
        ];
    }
}
