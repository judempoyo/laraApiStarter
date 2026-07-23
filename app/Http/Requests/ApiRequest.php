<?php
namespace App\Http\Requests;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->messages(); 

        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = implode(', ', $messages);
        }

        throw new HttpResponseException(
            ApiResponse::error(
                ErrorCode::VALIDATION_FAILED,
                'Validation failed',
                422,
                null,
                $formattedErrors 
            )
        );

    }
}
