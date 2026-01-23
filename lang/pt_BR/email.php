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

    'greetings' => [
        'subject' => 'Olá, :name!',
        'welcome' => 'Bem-vindo(a) à Agrofast!',
        'farewell' => 'Até logo, :name!',
    ],

    'account_inactivation_confirmation' => [
        'subject' => 'Exclusão de Conta',
        'title' => 'Confirmação de Exclusão de Conta',
        'farewell' => 'Lamentamos pela sua saída, estaremos aqui sempre que precisar.',
        'message' => 'Recebemos sua solicitação para excluir sua conta. Caso essa ação tenha sido intencional, não é necessária nenhuma ação adicional. Se não for o caso, entre em contato imediatamente com nosso suporte.',
    ],

    'authentication' => [
        'subject' => 'Código de autenticação',
        'welcome' => 'Bem-vindo de volta, :name!',
        'verification_instructions' => 'Utilize o código de verificação abaixo para confirmar seu login:',
        'session_verification' => 'Clique no botão abaixo para verificar sua sessão:',
        'verify_button' => 'Verificar Sessão',
        'expiration_notice' => 'Este link expira em :expires.',
    ],

    'email_change_confirmation' => [
        'subject' => 'Troca de E-mail',
        'title' => 'Confirmação de Alteração de E-mail',
        'message' => 'Você solicitou a alteração do seu endereço de e-mail. Para confirmar essa mudança, utilize o código abaixo:',
        'button' => 'Confirmar E-mail',
        'expiration' => 'Este link expira em :expires.',
    ],

    'first_login' => [
        'subject' => 'Confirme seu primeiro acesso',
        'title' => 'Bem-vindo à Agrofast!',
        'message' => 'Obrigado por se cadastrar! Para confirmar seu primeiro acesso, utilize o código de verificação abaixo:',
        'confirm_button' => 'Confirmar Acesso',
        'expiration' => 'Este link expira em :expires.',
    ],

    'new_device_login_notification' => [
        'subject' => 'Novo Login em Dispositivo',
        'title' => 'Novo Login em Dispositivo Não Reconhecido',
        'message' => 'Detectamos um login em sua conta a partir de um dispositivo ou localização que não reconhecemos. Se foi você, desconsidere esta mensagem. Caso contrário, recomendamos que você altere sua senha imediatamente e entre em contato com nosso suporte.',
    ],

    'password_change_notification' => [
        'subject' => 'Alteração de Senha',
        'title' => 'Notificação de Alteração de Senha',
        'message' => 'Sua senha foi alterada com sucesso. Se você não realizou essa alteração, entre em contato com nosso suporte imediatamente.',
    ],

    'password_recover' => [
        'subject' => 'Recuperação de Senha',
        'title' => 'Recuperação de Senha',
        'message' => 'Você solicitou a recuperação de sua senha. Utilize o código abaixo para redefinir sua senha:',
        'reset_button' => 'Redefinir Senha',
        'expiration' => 'Este link expira em :expires.',
    ],

    'suspicious_activity_notification' => [
        'subject' => 'Atividade Suspeita',
        'title' => 'Atividade Suspeita Detectada',
        'message' => 'Detectamos uma atividade suspeita em sua conta. Se você não reconhece essa ação, recomendamos alterar sua senha e entrar em contato com o suporte.',
    ],

    'two_factor_setup_notification' => [
        'subject' => 'Autenticação de Dois Fatores',
        'title' => 'Configuração de Autenticação de Dois Fatores',
        'message' => 'Você solicitou ativar a autenticação de dois fatores para aumentar a segurança de sua conta. Para concluir a configuração, utilize o código de verificação abaixo:',
        'expiration' => 'Este código expira em :expires.',
    ],
];
