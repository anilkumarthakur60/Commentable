<?php

namespace Anil\Comments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'commenter_id' => $this->commenter_id,
            'commenter_type' => $this->commenter_type,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'comment' => $this->comment,
            'approved' => $this->approved,
            'child_id' => $this->child_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'commenter' => $this->commenter,
            'commentable' => $this->whenLoaded('commentable'),
            'reactions' => $this->whenLoaded('reactions', function () {
                return $this->reactions
                    ->groupBy('type')
                    ->map(fn ($group) => $group->count());
            }),
        ];
    }
}
