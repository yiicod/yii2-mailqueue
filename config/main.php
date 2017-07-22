<?php

return [
    'modelMap' => [
        'mailQueue' => [
            'class' => 'yiicod\mailqueue\models\MailQueueModel',
        ],
    ],
    'commandMap' => [
        'mail-queue' => [
            'class' => 'yiicod\mailqueue\commands\MailQueueCommand',
        ],
    ],
];
