<?php

namespace yiicod\mailqueue\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yiicod\base\models\behaviors\AttributesMapBehavior;

/**
 * This is the model class for table "MailQueue".
 *
 * The followings are the available columns in table 'mailQueue':
 *
 * @property string $id
 * @property string $to
 * @property string $subject
 * @property string $mailer
 * @property string $body
 * @property int $priority
 * @property string $status
 * @property string $dateCreate
 */
class MailQueueModel extends ActiveRecord implements MailRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mail_queue';
    }

    /**
     * @return array validation rules for model attributes
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['to', 'subject', 'body'], 'required'],
            [['to', 'subject'], 'string', 'max' => 255],
            ['status', 'string', 'max' => 1],
            [array_values(static::attributesMap()), 'safe'],
        ];
    }

    /**
     * Set field attachs
     *
     * @param array $value
     */
    public function setAttaches(array $value)
    {
        if (in_array('attaches', $this->attributes())) {
            $this->attaches = Json::encode($value);
        }
    }

    /**
     * Get field attachs
     *
     * @return array
     */
    public function getAttaches(): array
    {
        $value = null;
        if (in_array('attaches', $this->attributes())) {
            $value = Json::decode($this->attaches);
        }

        return null === $value ? [] : $value;
    }

    public function getData(): array
    {
        return [
            'to' => $this->to,
            'subject' => $this->subject,
            'mailer' => $this->mailer,
            'body' => $this->body,
            'priority' => $this->priority,
            'status' => $this->status ?: 0, // @todo Think about this
            'attaches' => $this->getAttaches(),
        ];
    }

    public function push(): bool
    {
        $this->setAttributes($this->getData());

        return $this->save();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('mailqueue', 'Id'),
            'to' => Yii::t('mailqueue', 'To'),
            'mailer' => Yii::t('mailqueue', 'Mailer'),
            'subject' => Yii::t('mailqueue', 'Subject'),
            'body' => Yii::t('mailqueue', 'Body'),
            'priority' => Yii::t('mailqueue', 'Priority'),
            'status' => Yii::t('mailqueue', 'Status'),
        ];
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
     * @param string $className active record class name
     *
     * @return MailQueueModel the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function attributesMap()
    {
        return [
            'fieldFrom' => 'from',
            'fieldTo' => 'to',
            'fieldMailer' => 'mailer',
            'fieldSubject' => 'subject',
            'fieldBody' => 'body',
            'fieldPriority' => 'priority',
            'fieldAttaches' => 'attaches',
            'fieldStatus' => 'status',
            'fieldCreatedDate' => 'created_date',
            'fieldUpdatedDate' => 'updated_date',
        ];
    }

    public function behaviors()
    {
        $behaviors = [
            'attributesMapBehavior' => [
                'class' => AttributesMapBehavior::class,
                'attributesMap' => static::attributesMap(),
            ],
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => static::attributesMap()['fieldCreatedDate'],
                'updatedAtAttribute' => static::attributesMap()['fieldUpdatedDate'],
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
            'XssBehavior' => [
                'class' => '\yiicod\base\models\behaviors\XssBehavior',
                'attributesExclude' => [static::attributesMap()['fieldBody']],
            ],
        ];

        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }
}
