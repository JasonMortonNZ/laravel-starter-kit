<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeamInvitation
 */
final class PendingInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{code: string, inviterName: string, team: array{name: string, slug: string}}
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'inviterName' => $this->inviter->name,
            'team' => [
                'name' => $this->team->name,
                'slug' => $this->team->slug,
            ],
        ];
    }
}
