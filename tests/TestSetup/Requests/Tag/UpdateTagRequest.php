<?php

namespace Anil\Comments\Tests\TestSetup\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('id'); // Changed to use route() directly

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:tags,name,{$id}",
            ],

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
