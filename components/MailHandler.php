<?php

namespace yiicod\mailqueue\components;

use Yii;
use yiicod\mailqueue\models\MailRepositoryInterface;

class MailHandler implements MailHandlerInterface
{
    const STATUS_SENT = 1;

    const STATUS_PENDING = 0;

    const STATUS_FAILED = 2;

    /**
     * Find mails
     *
     * @return mixed
     */
    public function findAll()
    {
        $class = Yii::$app->get('mailqueue')->modelMap['mailQueue']['class'];
        $models = $class::find()
            ->where(sprintf('%s=:pending', $class::attributesMap()['fieldStatus']))
            ->params([':pending' => self::STATUS_PENDING])
            ->orderBy($class::attributesMap()['fieldPriority'])
            ->limit(60)
            ->all();

        return $models;
    }

    /**
     * @param MailRepositoryInterface $item
     *
     * @return bool
     */
    public function send(MailRepositoryInterface $item): bool
    {
        if (Yii::$app->has($item->mailer ?? '')) {
            $mailer = Yii::$app->get($item->mailer);
        } else {
            $mailer = Yii::$app->mailer;
        }
        $message = $mailer->compose(null, ['item' => $item]);
        $message->setTo($item->to)
            ->setSubject($item->subject)
            ->setHtmlBody($item->body);
        if (false === empty($item->from) && true === empty($message->getFrom())) {
            $message->setFrom($item->from);
        }
        foreach ($item->getAttaches() as $attach) {
            $message->attach($attach);
        }

        return $message->send();
    }

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function success(array $ids)
    {
        $class = Yii::$app->get('mailqueue')->modelMap['mailQueue']['class'];
        $class::updateAll([
            $class::attributesMap()['fieldStatus'] => self::STATUS_SENT,
            $class::attributesMap()['fieldCreatedDate'] => date('Y-m-d H:i:s'),
        ], ['id' => $ids]);
    }

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function failed(array $ids)
    {
        $class = Yii::$app->get('mailqueue')->modelMap['mailQueue']['class'];
        $class::updateAll([
            $class::attributesMap()['fieldStatus'] => self::STATUS_FAILED,
            $class::attributesMap()['fieldCreatedDate'] => date('Y-m-d H:i:s'),
        ], ['id' => $ids]);
    }
}
