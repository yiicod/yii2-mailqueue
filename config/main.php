<?php

return [
    'modelMap' => [
        'mailQueue' => [
            'class' => \yiicod\mailqueue\models\MailQueueModel::class,
        ],
    ],
    'commandMap' => [
        'mail-queue' => [
            'class' => \yiicod\mailqueue\commands\WorkerCommand::class,
        ],
    ],
];
