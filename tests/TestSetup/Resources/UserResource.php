<?php

namespace Anil\Comments\Tests\TestSetup\Resources;

use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserModel
 */
class UserResource extends JsonResource
{
    public function __construct(UserModel $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this['id'],
            'name'       => $this['name'],
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
        ];
    }
}
