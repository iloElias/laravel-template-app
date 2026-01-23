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
        "subject" => "Eliminación de Cuenta",
        "title" => "Confirmación de Eliminación de Cuenta",
        "farewell" => "Lamentamos tu partida, estaremos aquí siempre que lo necesites.",
        "message" => "Hemos recibido tu solicitud para eliminar tu cuenta. Si esta acción fue intencional, no se requiere ninguna acción adicional. De lo contrario, contacta inmediatamente a nuestro soporte.",
    ],

    'authentication' => [
        "subject" => "Código de autenticación",
        "welcome" => "¡Bienvenido de nuevo, :name!",
        "verification_instructions" => "Utiliza el código de verificación a continuación para confirmar tu inicio de sesión:",
        "session_verification" => "Haz clic en el botón de abajo para verificar tu sesión:",
        "verify_button" => "Verificar Sesión",
        "expiration_notice" => "Este enlace expira en :expires.",
    ],

    'email_change_confirmation' => [
        "subject" => "Cambio de E-mail",
        "title" => "Confirmación de Cambio de E-mail",
        "message" => "Has solicitado cambiar tu dirección de correo electrónico. Para confirmar este cambio, utiliza el código a continuación:",
        "button" => "Confirmar E-mail",
        "expiration" => "Este enlace expira en :expires.",
    ],

    'first_login' => [
        "subject" => "Confirma tu primer acceso",
        "title" => "¡Bienvenido a Agrofast!",
        "message" => "¡Gracias por registrarte! Para confirmar tu primer acceso, utiliza el código de verificación a continuación:",
        "confirm_button" => "Confirmar Acceso",
        "expiration" => "Este enlace expira en :expires.",
    ],

    'new_device_login_notification' => [
        "subject" => "Nuevo inicio de sesión en un dispositivo",
        "title" => "Nuevo inicio de sesión en un dispositivo no reconocido",
        "message" => "Hemos detectado un inicio de sesión en tu cuenta desde un dispositivo o ubicación que no reconocemos. Si fuiste tú, ignora este mensaje. En caso contrario, te recomendamos cambiar tu contraseña inmediatamente y ponerte en contacto con nuestro soporte.",
    ],

    'password_change_notification' => [
        "subject" => "Cambio de contraseña",
        "title" => "Notificación de cambio de contraseña",
        "message" => "Tu contraseña fue cambiada exitosamente. Si no realizaste este cambio, contacta inmediatamente con nuestro soporte.",
    ],

    'password_recover' => [
        "subject" => "Recuperación de contraseña",
        "title" => "Recuperación de contraseña",
        "message" => "Has solicitado la recuperación de tu contraseña. Utiliza el código a continuación para restablecer tu contraseña:",
        "reset_button" => "Restablecer contraseña",
        "expiration" => "Este enlace expira en :expires.",
    ],

    'suspicious_activity_notification' => [
        "subject" => "Actividad sospechosa",
        "title" => "Actividad sospechosa detectada",
        "message" => "Hemos detectado una actividad sospechosa en tu cuenta. Si no reconoces esta acción, te recomendamos cambiar tu contraseña y contactar al soporte.",
    ],

    'two_factor_setup_notification' => [
        "subject" => "Autenticación de dos factores",
        "title" => "Configuración de autenticación de dos factores",
        "message" => "Has solicitado activar la autenticación de dos factores para mejorar la seguridad de tu cuenta. Para completar la configuración, utiliza el código de verificación a continuación:",
        "expiration" => "Este código expira en :expires."
    ],

];
