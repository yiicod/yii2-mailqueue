<?php

namespace yiicod\mailqueue\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MailQueue".
 *
 * The followings are the available columns in table 'MailQueue':
 * @property string $id
 * @property string $to
 * @property string $subject
 * @property string $body
 * @property string $status
 * @property string $dateCreate
 */
class MailQueueModel extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MailQueue';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            [['to', 'subject', 'body'], 'required'],
            [['to', 'subject'], 'string', 'max' => 255],
            ['status', 'string', 'max' => 1],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            [['id, to, subject, body, priority, status'], 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('mailqueue', 'Id'),
            'to' => Yii::t('mailqueue', 'To'),
            'subject' => Yii::t('mailqueue', 'Subject'),
            'body' => Yii::t('mailqueue', 'Body'),
            'priority' => Yii::t('mailqueue', 'Priority'),
            'status' => Yii::t('mailqueue', 'Status'),
            'dateCreate' => Yii::t('mailqueue', 'Date Create'),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return MailQueueModel the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors()
    {
        $behaviors = [
            'attributesMapBehavior' => [
                'class' => '\yiicod\mailqueue\models\behaviors\AttributesMapBehavior',
                'attributesMap' => Yii::$app->get('mailqueue')->modelMap['MailQueue']
            ],
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => in_array(Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldCreateDate'], $this->attributes()) ?
                        Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldCreateDate'] : null,
                'updatedAtAttribute' => in_array(Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldUpdateDate'], $this->attributes()) ?
                        Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldUpdateDate'] : null,
                'value' => function() {
                    return date("Y-m-d H:i:s");
                },
            ],
            'XssBehavior' => [
                'class' => '\yiicod\base\models\behaviors\XssBehavior',
                'attributesExclude' => array(Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldBody'])
            ]
        ];


        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

}
