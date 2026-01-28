<?php

return [
    'plugin' => [
        'name' => 'Campanha',
        'description' => 'Gestão de campanhas',
    ],
    'permissions' => [
        'manage' => 'Gerir campanhas',
    ],
    'components' => [
        'subscribe' => [
            'heading' => 'Subscrever a newsletter',
            'description' => 'Recebe as minhas novidades!',
            'form' => [
                'name' => 'Nome',
                'email' => 'E-mail',
                'button' => 'Subscrever',
            ],
            'privacy' => 'Respeito a tua <a href=":privacyLink">privacidade</a>. Podes cancelar a subscrição a qualquer momento.',
            'feedback' => 'Verifica o teu e-mail para obter o link de confirmação!',
        ],
        'confirm' => [
            'success' => [
                'heading' => 'Subscrição confirmada!',
                'body' => 'Obrigado, :subscriber. A tua subscrição foi confirmada.',
            ],
            'error' => [
                'heading' => 'A subscrição falhou!',
                'body' => 'Este link de confirmação é inválido ou já foi utilizado.',
            ],
            'mail' => [
                'subject' => 'Confirme a tua subscrição',
                'body' => 'Por favor confirme a tua subscrição clicando no botão abaixo:',
                'button' => 'Confirmar subscrição',
                'ignore' => 'Se não solicitaste este pedido, podes ignorar este e-mail com segurança.',
            ],
        ],
    ],
];
