<?php

declare(strict_types=1);

return [
    // ── Auth — Registration ───────────────────────────────────────────────
    'register_success'           => 'User registered successfully. Please verify your email.',

    // ── Auth — Login ──────────────────────────────────────────────────────
    'login_success'              => 'Login successful.',
    'account_disabled'           => 'Your account has been disabled by an administrator.',

    // ── Auth — Logout ─────────────────────────────────────────────────────
    'logout_success'             => 'Logged out successfully.',
    'logout_all_success'         => 'Logged out from all devices successfully.',
    'session_revoked'            => 'Session revoked successfully.',
    'other_sessions_revoked'     => 'All other sessions revoked.',

    // ── Auth — Token ──────────────────────────────────────────────────────
    'token_refreshed'            => 'Token refreshed successfully.',

    // ── Auth — Email Verification ─────────────────────────────────────────
    'email_verified'             => 'Email verified successfully.',
    'email_already_verified'     => 'Email already verified.',
    'email_already_verified_err' => 'Your email is already verified.',
    'verification_link_sent'     => 'A new verification link has been sent.',
    'invalid_verification_link'  => 'Invalid verification link.',

    // ── Auth — Email Check ────────────────────────────────────────────────
    'email_exists'               => 'Email exists.',
    'email_not_found'            => 'Email not found.',

    // ── Auth — Profile ────────────────────────────────────────────────────
    'profile_updated'            => 'Profile updated successfully.',
    'email_changed'              => 'Email changed successfully. A new verification link has been sent.',
    'password_updated'           => 'Password updated successfully.',
    'wrong_current_password'     => 'The provided password does not match your current password.',
    'avatar_updated'             => 'Profile picture updated.',
    'avatar_deleted'             => 'Profile picture deleted.',

    // ── Auth — Sessions ───────────────────────────────────────────────────
    'sessions_retrieved'         => 'Sessions retrieved successfully.',

    // ── Two-Factor Authentication ─────────────────────────────────────────
    '2fa_setup'                  => 'Scan the QR code with your authenticator app, then confirm with the generated code.',
    '2fa_already_enabled'        => 'Two-factor authentication is already enabled.',
    '2fa_already_enabled_msg'    => 'Two-factor authentication is already active on this account.',
    '2fa_confirmed'              => 'Two-factor authentication has been enabled.',
    '2fa_verified'               => 'Two-factor code verified successfully.',
    '2fa_disabled'               => 'Two-factor authentication has been disabled.',
    '2fa_not_enabled'            => 'Two-factor authentication is not enabled.',
    '2fa_not_confirmed'          => 'Two-factor authentication has not been confirmed yet.',
    '2fa_invalid_code'           => 'The provided code is invalid or expired.',
    '2fa_invalid_password'       => 'The provided password is incorrect.',

    // ── API Keys ──────────────────────────────────────────────────────────
    'api_keys_retrieved'         => 'API keys retrieved successfully.',
    'api_key_created'            => 'API key created. Save the key now — it will not be shown again.',
    'api_key_revoked'            => 'API key revoked successfully.',
    'api_key_not_found'          => 'API key not found.',

    // ── Impersonation ─────────────────────────────────────────────────────
    'impersonation_started'      => 'Impersonation started.',
    'impersonation_stopped'      => 'Impersonation stopped.',
    'impersonation_self_error'   => 'You cannot impersonate yourself.',

    // ── Notifications ─────────────────────────────────────────────────────
    'notifications_retrieved'    => 'Notifications retrieved successfully.',
    'notification_read'          => 'Notification marked as read.',
    'notifications_all_read'     => 'All notifications marked as read.',
    'notification_deleted'       => 'Notification deleted.',

    // ── Preferences ───────────────────────────────────────────────────────
    'preferences_retrieved'      => 'Preferences retrieved successfully.',
    'preference_set'             => 'Preference saved.',
    'preference_deleted'         => 'Preference deleted.',

    // ── Health ────────────────────────────────────────────────────────────
    'health_ok'                  => 'All systems operational.',
    'health_degraded'            => 'One or more services are degraded.',

    // ── Auth (generic) ────────────────────────────────────────────────────
    'invalid_credentials'        => 'Invalid credentials.',
    'invalid_credentials_message' => 'The provided credentials are incorrect.',
    'unauthenticated'            => 'Unauthenticated.',
    'unauthenticated_message'    => 'You are not authenticated.',
    'unauthorized'               => 'Access denied.',
    'unauthorized_message'       => 'You do not have permission to perform this action.',
    'validation_failed'          => 'Validation failed.',
    'not_found'                  => ':resource not found.',
    'not_found_message'          => 'The requested :resource does not exist.',
    'conflict'                   => ':resource already exists.',
    'gone'                       => ':resource has been permanently removed.',
    'gone_message'               => 'The requested :resource no longer exists.',
    'too_many_requests'          => 'Too many requests.',
    'too_many_requests_message'  => 'You have exceeded your request limit. Please try again later.',
    'server_error'               => 'An unexpected error occurred.',
    'server_error_message'       => 'An unexpected error occurred. Please try again later.',
    'service_unavailable'        => 'Service temporarily unavailable.',
    'service_unavailable_message' => 'The service is temporarily unavailable. Please try again later.',

    // ── Webhooks ──────────────────────────────────────────────────────────
    'webhook_created'            => 'Webhook registered successfully.',
    'webhook_updated'            => 'Webhook updated successfully.',
    'webhook_deleted'            => 'Webhook deleted successfully.',
    'webhooks_retrieved'         => 'Webhooks retrieved successfully.',
    'webhook_deliveries'         => 'Webhook delivery history retrieved.',
    'webhook_redelivered'        => 'Webhook delivery queued for retry.',
    'webhook_not_found'          => 'Webhook not found.',
    'webhook_delivery_not_found' => 'Webhook delivery not found.',
    'available_events'           => 'Available webhook events retrieved.',

    // ── Media ─────────────────────────────────────────────────────────────
    'media_uploaded'             => 'File uploaded successfully.',
    'media_deleted'              => 'File deleted successfully.',
    'media_retrieved'            => 'Media files retrieved successfully.',
    'media_url_generated'        => 'Temporary URL generated.',
    'media_not_found'            => 'Media file not found.',
    'media_upload_failed'        => 'File upload failed.',

    // ── Exports ───────────────────────────────────────────────────────────
    'export_queued'              => 'Export queued. You will be notified when it is ready.',
    'export_retrieved'           => 'Exports retrieved successfully.',
    'export_not_found'           => 'Export not found.',
    'export_not_ready'           => 'Export is not ready for download yet.',
    'export_ready'               => 'Your export is ready for download.',
    'export_resources_listed'    => 'Available export resources retrieved.',
];
