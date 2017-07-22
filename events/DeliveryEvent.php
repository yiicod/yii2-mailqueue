<?php

namespace yiicod\mailqueue\events;

use yii\base\Event;
use yiicod\mailqueue\models\MailRepositoryInterface;

class DeliveryEvent extends Event
{
    /**
     * @var MailRepositoryInterface
     */
    public $models;

    public function __construct(array $models, array $config = [])
    {
        $this->models = $models;
        parent::__construct($config);
    }
}
