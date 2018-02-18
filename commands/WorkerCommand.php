<?php

namespace yiicod\mailqueue\commands;

use yii\console\Controller;
use yiicod\mailqueue\components\MailHandler;
use yiicod\mailqueue\components\MailHandlerInterface;
use yiicod\mailqueue\MailQueue;

/**
 * Console command
 * class MailQueueCommand
 */
class WorkerCommand extends Controller
{
    /**
     * @var MailHandlerInterface
     */
    public $mailProvider = MailHandler::class;

    /**
     * @var string
     */
    public $defaultAction = 'work';

    /**
     * @var int
     */
    public $delay = 15;

    /**
     * @throws \Exception
     */
    public function actionWork()
    {
        while (true) {
            MailQueue::delivery(new $this->mailProvider());

            sleep($this->delay);
        }
    }
}
