<?php

namespace yiicod\mailqueue\commands;

use Exception;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Console command
 * class MailQueueCommand
 */
class MailQueueCommand extends Controller
{

    /**
     * @var integer Limit of mail
     */
    public $limit = 60;

    /**
     * Condition string
     * @var string
     */
    public $condition = 'status=:status';

    /**
     * 
     * @var string 'priority DESC'
     */
    public $order = null;

    /**
     * Params for condition
     * @var array
     */
    public $params = array('status' => 0);

    /**
     * Time live file, 1 hour
     */
    public $timeLock = 3600;

    /**
     * Run send mail 
     */
    public function actionIndex()
    {
        $criteria = [];
        $criteria['where'] = $this->condition;
        $criteria['params'] = $this->params;
        $criteria['order'] = $this->order;
        $criteria['limit'] = $this->limit;

        //try {
            Yii::$app->mailQueue->delivery($criteria);
//        } catch (Exception $e) {
//            if (YII_DEBUG) {
//                Yii::error("MailQueueCommand: " . $e->getMessage(), 'system.mailqueue');
//            }
//        }
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
                    'LockUnLockBehavior' => [
                        'class' => 'yiicod\cron\commands\behaviors\LockUnLockBehavior',
                        'timeLock' => $this->timeLock
                    ]
                        ]
        );
    }

}
