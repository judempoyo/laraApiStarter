<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ErrorCode;
use App\Enums\PublicEnum;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Dedoc\Scramble\Attributes\Example;
use Dedoc\Scramble\Attributes\PathParameter;

class EnumController extends Controller
{
    #[PathParameter(
        name: 'enum',
        description: 'Enum public exposé par l’API',
        type: 'string',
        examples: [
            'error_code'                    => new Example(value: 'error-code'),
            'security_event'                => new Example(value: 'security-event'),
            'user_role'                     => new Example(value: 'user-role'),
            'user_status'                     => new Example(value: 'user-status'),
           
           /* 
            'result_login'                  => new Example(value: 'result-login'),
            'result_password_reset'         => new Example(value: 'result-password-reset'),
            'result_update_email'           => new Example(value: 'result-update-email'),
            'result_update_password'        => new Example(value: 'result-update-password'),
            'result_update_payment_status'  => new Example(value: 'result-update-payment-status'),
            'result_update_profile'         => new Example(value: 'result-update-profile'), */
        ]
    )]
    public function show(string $enum)
    {
        try {
            $publicEnum = PublicEnum::from($enum);
        } catch (\ValueError) {
            return ApiResponse::error(
                ErrorCode::ENUM_NOT_FOUND,
                'Enum not found.',
                404,
                'Enum not found'
            );
        }

        $enumClass = $publicEnum->class();

        return ApiResponse::success(
            array_column($enumClass::cases(), 'value'),
            "Enum {$enum} successfully fetched."
        );
    }
}
