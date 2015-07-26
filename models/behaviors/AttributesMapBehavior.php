<?php

namespace yiicod\mailqueue\models\behaviors;

use Yii;
use yii\helpers\BaseJson;
use yiicod\base\models\behaviors\AttributesMapBehavior as AttributesMapBehaviorBase;

class AttributesMapBehavior extends AttributesMapBehaviorBase
{

    /**
     * Set field attachs
     * @param Array $value
     */
    public function setAttachs(array $value)
    {
        if (in_array(Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldAttachs'], $this->owner->attributes())) {
            $this->owner->{Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldAttachs']} = BaseJson::encode($value);
        }
    }

    /**
     * Get field attachs
     * @return Array
     */
    public function getAttachs()
    {
        if (in_array(Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldAttachs'], $this->owner->attributes())) {
            $value = BaseJson::decode($this->owner->{Yii::$app->get('mailqueue')->modelMap['MailQueue']['fieldAttachs']});
            if (null === $value) {
                return [];
            }
            return $value;
        }
        return [];
    }

}
