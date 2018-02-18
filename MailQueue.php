<?php

namespace yiicod\mailqueue;

use Exception;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Event;
use yii\console\Application;
use yii\db\Command;
use yii\helpers\ArrayHelper;
use yiicod\base\helpers\LoggerMessage;
use yiicod\mailqueue\components\MailHandlerInterface;
use yiicod\mailqueue\events\DeliveredEvent;
use yiicod\mailqueue\events\DeliveredItemEvent;
use yiicod\mailqueue\events\DeliveryEvent;
use yiicod\mailqueue\events\DeliveryItemEvent;
use yiicod\mailqueue\models\MailRepositoryInterface;

/**
 * Comments extension settings
 *
 * @author Orlov Alexey <aaorlov88@gmail.com>
 */
class MailQueue extends Component implements BootstrapInterface
{
    const EVENT_BEFORE_DELIVERY = 'before_delivery';

    const EVENT_AFTER_DELIVERY = 'after_delivery';

    const EVENT_BEFORE_DELIVERY_ITEM = 'before_delivery_item';

    const EVENT_AFTER_DELIVERY_ITEM = 'after_delivery_item';

    /**
     * @var array Table settings
     */
    public $modelMap = [];

    /**
     * @var array
     */
    public $commandMap = [];

    public function bootstrap($app)
    {
        // Merge main extension config with local extension config
        $config = include(dirname(__FILE__) . '/config/main.php');
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->{$key} = ArrayHelper::merge($value, $this->{$key});
            } elseif (null === $this->{$key}) {
                $this->{$key} = $value;
            }
        }

        if (Yii::$app instanceof Application) {
            //Merge commands map
            Yii::$app->controllerMap = ArrayHelper::merge($this->commandMap, Yii::$app->controllerMap);
        }

        Yii::setAlias('@yiicod', realpath(dirname(__FILE__) . '/..'));
    }

    /**
     * Push mass
     *
     * @todo Think change this to Array<MailQueueInterface>. Add method getData():array to MailQueueInterface.
     *
     * array(
     *    array(
     *      'field name to' => '',
     *      'field name subject' => '',
     *      'field name body' => '',
     *      'field name priority' => '',
     *      'field name from' => '',
     *      'field name attaches' => '',
     *    )
     * )
     *
     * @param array $data
     * @param Command $db
     *
     * @return int Return int
     */
    public static function batch($data, $partSize = 100, $db = null)
    {
        $table = Yii::$app->mailqueue->modelMap['mailQueue']['class'];

        $db = (null === $db ? Yii::$app->db : $db);
        $columns = array_keys(reset($data));
        $items = array_chunk($data, $partSize);
        foreach ($items as $chunk) {
            $rows = array_map(function ($item) {
                return $item->getData();
            }, $chunk);
            //Reconnect for big duration
            $db->close();
            $db->open();
            $db->createCommand()
                ->batchInsert($table::tableName(), $columns, $rows)
                ->execute();
        }
        //Reconnect for stable db works
        $db->close();
        $db->open();
    }

    /**
     * Add mail to queue
     *
     * @param MailRepositoryInterface $mail
     *
     * @return bool
     */
    public static function push(MailRepositoryInterface $mail)
    {
        return $mail->push();
    }

    /**
     * Send mail from queue
     *
     * @param MailHandlerInterface $mailHandler
     */
    public static function delivery(MailHandlerInterface $mailHandler)
    {
        $successIds = [];
        $failedIds = [];

        $models = $mailHandler->findAll();

        Event::trigger(static::class, static::EVENT_BEFORE_DELIVERY, new DeliveryEvent($models));

        foreach ($models as $item) {
            try {
                Event::trigger(static::class, static::EVENT_BEFORE_DELIVERY_ITEM, new DeliveryItemEvent($item));
                //$mailer = Yii::$app->mailer;
                if ($isSuccess = $mailHandler->send($item)) {
                    Yii::info(LoggerMessage::trace('MailQueue send success to - {to}, subject - {subject}', ['{to}' => $item->to, '{subject}' => $item->subject]), 'system.mailqueue');
                    $successIds[] = $item->id;
                } else {
                    Yii::info(LoggerMessage::trace('MailQueue send failed to - {to}, subject - {subject}', ['{to}' => $item->to, '{subject}' => $item->subject]), 'system.mailqueue');
                    $failedIds[] = $item->id;
                }
                Event::trigger(static::class, static::EVENT_AFTER_DELIVERY_ITEM, new DeliveredItemEvent($isSuccess, $item));
            } catch (Exception $e) {
                $failedIds[] = $item->id;

                Yii::error(LoggerMessage::log($e));
            }
        }

        Event::trigger(static::class, static::EVENT_AFTER_DELIVERY, new DeliveredEvent($successIds, $failedIds));
        if (count($successIds)) {
            $mailHandler->success($successIds);
        }
        if (count($failedIds)) {
            $mailHandler->failed($failedIds);
        }
    }
}
