<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeamInvitation
 */
final class TeamInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{code: string, email: string, role: string, role_label: string, created_at: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'email' => $this->email,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
