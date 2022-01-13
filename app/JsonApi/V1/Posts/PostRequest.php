<?php

namespace App\JsonApi\V1\Posts;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PostRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'slug' => ['required', 'string', Rule::unique('posts', 'slug')],
            'tags' => JsonApiRule::toMany(),
            'title' => ['required', 'string'],
        ];
    }

}