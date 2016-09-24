<?php

namespace yiicod\mailqueue\components;

use CDbCriteria;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseJson;
use yii\console\Application;

class MailQueue extends Component
{

    public $afterSendDelete = false;

    /**
     *
     * @var type 
     */
    public $partSize = 50;

    /**
     * Push mass
     * array(
     *    array(
     *      'field name to' => '',
     *      'field name subject' => '',
     *      'field name body' => '',
     *      'field name priority' => '',
     *      'field name from' => '',
     *      'field name attachs' => '',
     *    )
     * )
     * @param Array $data
     * @return int Return int
     */
    public function pushMass($data)
    {
        $table = Yii::$app->get('mailqueue')->modelMap['MailQueue']['class'];
        $model = new $table();

        $prepareValues = [];
        $prepareKeys = [];
        $index = 1;
        foreach ($data as $item) {
            if (is_array($item)) {
                $prepareData = ArrayHelper::merge([
                            $model->fieldFrom => '',
                            $model->fieldTo => '',
                            $model->fieldSubject => '',
                            $model->fieldBody => '',
                            $model->fieldAttachs => [],
                            $model->fieldStatus => Yii::$app->get('mailqueue')->modelMap['MailQueue']['status']['unsended']
                                ], $item);
                $prepareData[$model->fieldAttachs] = BaseJson::encode($prepareData[$model->fieldAttachs]);
                if (in_array($model->fieldCreateDate, $model->attributes())) {
                    $prepareData[$model->fieldCreateDate] = date("Y-m-d H:i:s");
                }
                if (in_array($model->fieldUpdateDate, $model->attributes())) {
                    $prepareData[$model->fieldUpdateDate] = date("Y-m-d H:i:s");
                }
                $prepareKeys = empty($prepareKeys) ? array_keys($prepareData) : $prepareKeys;
                $prepareValues[] = array_values($prepareData);
            }

            if (($index % $this->partSize === 0 || $index >= count($data)) && false === empty($prepareValues)) {
                //Reconnect for big duration
                Yii::$app->db->close();
                Yii::$app->db->open();
                Yii::$app->db
                        ->createCommand()
                        ->batchInsert($table::tableName(), $prepareKeys, $prepareValues)
                        ->execute();
                $prepareValues = [];
            }
            $index++;
        }
        //Reconnect for db stable works 
        Yii::$app->db->close();
        Yii::$app->db->open();
    }

    /**
     * Add mail from queue
     * @param string $to Email to
     * @param string $subject Email subject
     * @param string Body email, html
     * @param string|Array From email
     * @param string Attach for email array('path' => 'file path', 'name' => 'file bname')
     */
    public function push($to, $subject, $body, $priority = 0, $from = '', array $attachs = [], $additionalFields = [])
    {
        $table = Yii::$app->get('mailqueue')->modelMap['MailQueue']['class'];
        $model = new $table();

        $model->from = $from;
        $model->to = $to;
        $model->subject = $subject;
        $model->body = $body;
        $model->setAttachs($attachs);
        $model->status = Yii::$app->get('mailqueue')->modelMap['MailQueue']['status']['unsended'];

        if (in_array($model->fieldPriority, $model->attributes())) {
            $model->priority = $priority;
        }

        foreach ($additionalFields as $field => $value) {
            $model->{$field} = $value;
        }

        return $model->save();
    }

    /**
     * Send mail from queue
     * @param CDbCriteria
     */
    public function delivery($criteria)
    {
        $criteria = ArrayHelper::merge([
                    'where' => [],
                    'params' => [],
                    'order' => null,
                    'limit' => 0
                        ], $criteria);
        $table = Yii::$app->get('mailqueue')->modelMap['MailQueue']['class'];
        $deliveringCount = $criteria['limit'];
        $item = null;
        $ids = [];
        $failedIds = [];
        $statusSended = Yii::$app->get('mailqueue')->modelMap['MailQueue']['status']['sended'];
        $statusUnsended = Yii::$app->get('mailqueue')->modelMap['MailQueue']['status']['unsended'];
        $statusFailed = Yii::$app->get('mailqueue')->modelMap['MailQueue']['status']['failed'];
        $fieldStatus = Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldStatus'];
        $mailer = Yii::$app->{Yii::$app->get('mailqueue')->mailer};

        while ($deliveringCount > 0) {
            $criteria['limit'] = min($this->partSize, $deliveringCount);
            $models = $table::find()
                    ->where($criteria['where'])
                    ->params($criteria['params'])
                    ->orderBy($criteria['order'])
                    ->limit($criteria['limit'])
                    ->all();

            if (method_exists($mailer, 'deliveryBegin')) {
                $mailer->deliveryBegin($models);
            }
            foreach ($models as $item) {
                $attachs = $item->getAttachs();
                $mailer = Yii::$app->{Yii::$app->get('mailqueue')->mailer};
                $message = $mailer->compose();
                $message->setTo($item->to)
                        ->setSubject($item->subject)
                        ->setHtmlBody($item->body);
                if ($item->from) {
                    $message->setFrom($item->from);
                }
                if (is_array($attachs)) {
                    foreach ($attachs as $attach) {
                        $message->attach($attach);
                    }
                }
                if ($message->send()) {
                    $ids[] = $item->id;
                } else {
                    if (YII_DEBUG && Yii::$app instanceof Application) {
                        echo "MailQueue send failed to - $item->to, subject - $item->subject \n";
                    }
                    Yii::error("MailQueue send failed to - $item->to, subject - $item->subject \n", 'system.mailqueue');
                    $failedIds[] = $item->id;
                }
            }

            if (method_exists($mailer, 'deliveryEnd')) {
                $mailer->deliveryEnd($ids, $failedIds);
            }

            if (count($ids)) {
                if ($this->afterSendDelete) {
                    $table::deleteAll(['id' => $ids]);
                } else if (in_array($fieldStatus, $item->attributes())) {
                    $status = $statusSended;
                    $this->updateMailQueue($ids, $status);
                }
            }
            if (count($failedIds) && in_array($fieldStatus, $item->attributes())) {
                $status = $statusUnsended;
                if ($statusFailed != $statusUnsended) {
                    $status = $statusFailed;
                }
                $this->updateMailQueue($failedIds, $status);
            }

            $deliveringCount = $deliveringCount - $this->partSize;
        }
    }

    protected function updateMailQueue($ids, $status)
    {
        $table = Yii::$app->get('mailqueue')->modelMap['MailQueue']['class'];
        $model = new $table();
        $fieldStatus = Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldStatus'];
        $fieldUpdate = Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldUpdateDate'];

        if (in_array($fieldUpdate, $model->attributes())) {
            $table::updateAll([
                $fieldStatus => $status,
                $fieldUpdate => date("Y-m-d H:i:s")
                    ], ['id' => $ids]);
        } else {
            $table::updateAll([
                $fieldStatus => $status
                    ], ['id' => $ids]);
        }
    }

}
