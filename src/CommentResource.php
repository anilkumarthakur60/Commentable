<?php

namespace Anil\Comments;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
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
            'commentable' => $this->commentable,
        ];
    }
}
