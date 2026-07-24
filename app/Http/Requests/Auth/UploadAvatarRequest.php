<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class UploadAvatarRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select a profile picture.',
            'avatar.image'    => 'The file must be an image.',
            'avatar.mimes'    => 'The format must be: jpg, jpeg, png, or webp.',
            'avatar.max'      => 'The photo must not exceed 2 MB.',
        ];
    }
}
