<?php

declare(strict_types=1);

use App\Enums\ErrorCode;

return [
    'ui' => [
        'page_title' => 'API Error Catalogue (RFC 7807) — LaraApiStarter',
        'badge_rfc' => 'RFC 7807 Problem Details',
        'badge_standard' => 'Standardized Errors',
        'heading' => 'API Error Catalogue',
        'subtitle' => 'Official specification and developer reference for application error codes returned by the API. Compliant with RFC 7807 Problem Details.',
        'search_placeholder' => 'Filter by code, HTTP status, or keyword (press "/" to focus)...',
        'all_categories' => 'All Categories',
        'all_statuses' => 'All Statuses',
        'showing_count' => 'Showing :shown of :total error codes',
        'copy_link' => 'Copy link',
        'copied' => 'Copied!',
        'copy_code' => 'Copy code',
        'copy_payload' => 'Copy JSON payload',
        'example_payload' => 'RFC 7807 Example Payload',
        'api_docs' => 'API Reference (Scalar)',
        'github' => 'GitHub Repository',
        'no_results' => 'No error codes match your filter criteria.',
        'reset_filters' => 'Reset filters',
        'theme_toggle' => 'Toggle theme',
        'language' => 'Language',
    ],
    'codes' => [
        ErrorCode::UNAUTHENTICATED->value => [
            'title' => 'Unauthenticated',
            'description' => 'Authentication credentials (Bearer token or session) are missing, invalid, or expired.',
        ],
        ErrorCode::UNAUTHORIZED->value => [
            'title' => 'Access Denied',
            'description' => 'The authenticated user does not have permission or role to perform this action.',
        ],
        ErrorCode::USER_NOT_FOUND->value => [
            'title' => 'User Not Found',
            'description' => 'The specified user account could not be found in the system.',
        ],
        ErrorCode::EMAIL_NOT_VERIFIED->value => [
            'title' => 'Email Not Verified',
            'description' => 'Email address verification is required before performing this action.',
        ],
        ErrorCode::EMAIL_ALREADY_VERIFIED->value => [
            'title' => 'Email Already Verified',
            'description' => 'The user email address has already been verified.',
        ],
        ErrorCode::VALIDATION_FAILED->value => [
            'title' => 'Validation Failed',
            'description' => 'One or more request parameters failed validation constraints.',
        ],
        ErrorCode::INVALID_CREDENTIALS->value => [
            'title' => 'Invalid Credentials',
            'description' => 'The provided email or password does not match system records.',
        ],
        ErrorCode::INVALID_VALIDATION_LINK->value => [
            'title' => 'Invalid Verification Link',
            'description' => 'The email verification URL is invalid, tampered with, or expired.',
        ],
        ErrorCode::PASSWORD_MISMATCH->value => [
            'title' => 'Password Mismatch',
            'description' => 'The current password provided does not match your active account password.',
        ],
        ErrorCode::RATE_LIMIT_EXCEEDED->value => [
            'title' => 'Rate Limit Exceeded',
            'description' => 'Too many requests were sent in a given window. Please slow down and retry later.',
        ],
        ErrorCode::ACTION_NOT_ALLOWED->value => [
            'title' => 'Action Not Allowed',
            'description' => 'This operation is prohibited in the current resource state or environment.',
        ],
        ErrorCode::ENUM_NOT_FOUND->value => [
            'title' => 'Enum Not Found',
            'description' => 'The requested enumeration type or identifier does not exist.',
        ],
        ErrorCode::NEED_TO_BE_ADMIN->value => [
            'title' => 'Administrator Role Required',
            'description' => 'This operation requires an administrator role with elevated privileges.',
        ],
        ErrorCode::ACCOUNT_DISABLED->value => [
            'title' => 'Account Disabled',
            'description' => 'This account has been disabled or suspended by an administrator.',
        ],
        ErrorCode::RESET_PASSWORD_FAILED->value => [
            'title' => 'Password Reset Failed',
            'description' => 'Unable to complete password reset. The reset token may be invalid or expired.',
        ],
        ErrorCode::NOT_FOUND->value => [
            'title' => 'Not Found',
            'description' => 'The requested URL or endpoint does not exist on this server.',
        ],
        ErrorCode::SERVER_ERROR->value => [
            'title' => 'Internal Server Error',
            'description' => 'An unexpected server error occurred while processing the request.',
        ],
        ErrorCode::FORBIDDEN->value => [
            'title' => 'Forbidden',
            'description' => 'Access to the target resource is strictly forbidden.',
        ],
        ErrorCode::METHOD_NOT_ALLOWED->value => [
            'title' => 'Method Not Allowed',
            'description' => 'The HTTP method used is not supported for this endpoint.',
        ],
        ErrorCode::RESOURCE_NOT_FOUND->value => [
            'title' => 'Resource Not Found',
            'description' => 'The requested entity or resource was not found.',
        ],
        ErrorCode::CONFLICT->value => [
            'title' => 'Resource Conflict',
            'description' => 'The request conflicts with current server state or resource uniqueness.',
        ],
        ErrorCode::TOO_MANY_REQUESTS->value => [
            'title' => 'Too Many Requests',
            'description' => 'The request quota has been exceeded for this API client or IP.',
        ],
        ErrorCode::GONE->value => [
            'title' => 'Resource Gone',
            'description' => 'The resource was permanently deleted and is no longer available.',
        ],
        ErrorCode::UNPROCESSABLE->value => [
            'title' => 'Unprocessable Entity',
            'description' => 'The server understands the payload format but cannot process the contained instructions.',
        ],
        ErrorCode::SERVICE_UNAVAILABLE->value => [
            'title' => 'Service Unavailable',
            'description' => 'The server is currently unavailable or undergoing scheduled maintenance.',
        ],
        ErrorCode::TWO_FACTOR_ALREADY_ENABLED->value => [
            'title' => '2FA Already Enabled',
            'description' => 'Two-factor authentication is already active for this account.',
        ],
        ErrorCode::TWO_FACTOR_NOT_ENABLED->value => [
            'title' => '2FA Not Enabled',
            'description' => 'Two-factor authentication has not been enabled on this account.',
        ],
        ErrorCode::TWO_FACTOR_INVALID_CODE->value => [
            'title' => 'Invalid 2FA Code',
            'description' => 'The provided time-based one-time password (TOTP) code is invalid or expired.',
        ],
        ErrorCode::TWO_FACTOR_NOT_CONFIRMED->value => [
            'title' => '2FA Not Confirmed',
            'description' => 'Two-factor enrollment has been initiated but not yet confirmed with a valid OTP.',
        ],
        ErrorCode::TWO_FACTOR_INVALID_RECOVERY_CODE->value => [
            'title' => 'Invalid Recovery Code',
            'description' => 'The supplied backup recovery code is invalid or has already been consumed.',
        ],
        ErrorCode::TWO_FACTOR_REQUIRED->value => [
            'title' => '2FA Verification Required',
            'description' => 'A two-factor authentication challenge must be completed to proceed.',
        ],
        ErrorCode::API_KEY_INVALID->value => [
            'title' => 'Invalid API Key',
            'description' => 'The provided API key is invalid, malformed, or missing.',
        ],
        ErrorCode::API_KEY_EXPIRED->value => [
            'title' => 'API Key Expired',
            'description' => 'The API key has passed its expiration date and is no longer valid.',
        ],
        ErrorCode::API_KEY_REVOKED->value => [
            'title' => 'API Key Revoked',
            'description' => 'The API key has been revoked and can no longer be used.',
        ],
        ErrorCode::IMPERSONATION_NOT_ALLOWED->value => [
            'title' => 'Impersonation Not Allowed',
            'description' => 'You do not have permission to impersonate this user account.',
        ],
        ErrorCode::IMPERSONATION_SELF->value => [
            'title' => 'Self Impersonation Not Allowed',
            'description' => 'An administrator cannot impersonate their own account.',
        ],
        ErrorCode::SUSPICIOUS_REQUEST->value => [
            'title' => 'Suspicious Request Blocked',
            'description' => 'The request was blocked by security filters (SQL injection, XSS, or path traversal detected).',
        ],
        ErrorCode::REQUEST_TOO_LARGE->value => [
            'title' => 'Request Payload Too Large',
            'description' => 'The request payload size exceeds the maximum allowed upload or body threshold.',
        ],
        ErrorCode::WEBHOOK_NOT_FOUND->value => [
            'title' => 'Webhook Not Found',
            'description' => 'The requested webhook subscription was not found.',
        ],
        ErrorCode::WEBHOOK_DELIVERY_FAILED->value => [
            'title' => 'Webhook Delivery Failed',
            'description' => 'The webhook payload could not be delivered to the configured remote endpoint.',
        ],
        ErrorCode::MEDIA_NOT_FOUND->value => [
            'title' => 'Media Not Found',
            'description' => 'The requested media item was not found in storage.',
        ],
        ErrorCode::MEDIA_UPLOAD_FAILED->value => [
            'title' => 'Media Upload Failed',
            'description' => 'The media file could not be uploaded or processed.',
        ],
        ErrorCode::EXPORT_NOT_FOUND->value => [
            'title' => 'Export Not Found',
            'description' => 'The requested export job or downloaded file was not found or has expired.',
        ],
        ErrorCode::EXPORT_FAILED->value => [
            'title' => 'Export Failed',
            'description' => 'The asynchronous data export operation failed during processing.',
        ],
    ],
];
