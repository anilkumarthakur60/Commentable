<?php

namespace Anil\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class ReactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var list<string> $allowedTypes */
        $allowedTypes = Config::get('comments.reactions.types', ['like', 'dislike']);

        return [
            'type' => ['required', 'string', Rule::in($allowedTypes)],
        ];
    }
}
