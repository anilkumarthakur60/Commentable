<?php

namespace Anil\Comments\Http\Requests;

use Anil\Comments\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (Config::get('comments.guest_commenting')) {
            return true;
        }

        if ($this->user() === null) {
            return false;
        }

        return Gate::allows('create-comment', Comment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'min:1'],
            'message' => Config::get('comments.validation.message', ['required', 'string']),
        ];

        if (! $this->user()) {
            $rules['guest_name'] = Config::get('comments.validation.guest_name', ['required', 'string', 'max:255']);
            $rules['guest_email'] = Config::get('comments.validation.guest_email', ['required', 'string', 'email', 'max:255']);
        }

        return $rules;
    }
}
