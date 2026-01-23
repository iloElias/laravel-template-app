<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for various email notifications
    | and messages sent to users. You are free to modify these language
    |
    */

    'account_inactivation_confirmation' => [
        "subject" => "Account Deletion",
        "title" => "Account Deletion Confirmation",
        "farewell" => "We're sorry to see you go; we'll always be here if you need us.",
        "message" => "We've received your request to delete your account. If this action was intentional, no further steps are necessary. If not, please contact our support immediately.",
    ],

    'authentication' => [
        "subject" => "Authentication Code",
        "welcome" => "Welcome back, :name!",
        "verification_instructions" => "Use the verification code below to confirm your login:",
        "session_verification" => "Click the button below to verify your session:",
        "verify_button" => "Verify Session",
        "expiration_notice" => "This link expires in :expires.",
    ],

    'email_change_confirmation' => [
        "subject" => "Email Change",
        "title" => "Email Change Confirmation",
        "message" => "You requested to change your email address. To confirm this change, use the code below:",
        "button" => "Confirm Email",
        "expiration" => "This link expires in :expires.",
    ],

    'first_login' => [
        "subject" => "Confirm Your First Login",
        "title" => "Welcome to Agrofast!",
        "message" => "Thank you for signing up! To confirm your first login, use the verification code below:",
        "confirm_button" => "Confirm Login",
        "expiration" => "This link expires in :expires.",
    ],

    'new_device_login_notification' => [
        "subject" => "New Login from Device",
        "title" => "New Login on Unrecognized Device",
        "message" => "We've detected a login to your account from an unrecognized device or location. If it was you, please disregard this message. Otherwise, we recommend that you change your password immediately and contact our support.",
    ],

    'password_change_notification' => [
        "subject" => "Password Change",
        "title" => "Password Change Notification",
        "message" => "Your password has been successfully changed. If you did not initiate this change, please contact our support immediately.",
    ],

    'password_recover' => [
        "subject" => "Password Recovery",
        "title" => "Password Recovery",
        "message" => "You requested to recover your password. Use the code below to reset your password:",
        "reset_button" => "Reset Password",
        "expiration" => "This link expires in :expires.",
    ],

    'suspicious_activity_notification' => [
        "subject" => "Suspicious Activity",
        "title" => "Suspicious Activity Detected",
        "message" => "We have detected suspicious activity in your account. If you do not recognize this action, we recommend that you change your password and contact support.",
    ],

    'two_factor_setup_notification' => [
        "subject" => "Two-Factor Authentication",
        "title" => "Two-Factor Authentication Setup",
        "message" => "You requested to enable two-factor authentication to increase your account's security. To complete the setup, use the verification code below:",
        "expiration" => "This code expires in :expires."
    ],

];
