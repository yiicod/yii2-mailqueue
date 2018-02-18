<?php

namespace yiicod\mailqueue\commands;

use Exception;
use Yii;
use yiicod\base\helpers\LoggerMessage;
use yiicod\cron\commands\DaemonController;
use yiicod\mailqueue\components\MailHandler;
use yiicod\mailqueue\components\MailHandlerInterface;
use yiicod\mailqueue\MailQueue;

/**
 * Console command
 * class MailQueueCommand
 */
class MailQueueCommand extends DaemonController
{
    /**
     * @var MailHandlerInterface
     */
    public $mailProvider = MailHandler::class;

    /**
     * Only one command can run in the same time in {n} times
     *
     * @param int $timeLock
     */
    public $timeLock = 3600;

    /**
     * Daemon name
     *
     * @return string
     */
    protected function daemonName(): string
    {
        return 'mail-queue';
    }

    /**
     * Run send mail
     */
    public function worker()
    {
        try {
            Yii::$app->db->close();
            Yii::$app->db->open();
            MailQueue::delivery(new $this->mailProvider());
        } catch (Exception $e) {
            Yii::error(LoggerMessage::log($e), __METHOD__);
        }
    }
}
