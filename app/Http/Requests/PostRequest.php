<?php

namespace App\Http\Requests;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Foundation\Http\FormRequest;


class PostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => $this->isMethod('post') ? 'required|image|max:2048' : 'nullable|image|max:2048',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }



    public function authorize()
    {
        return auth()->check();
    }
}
