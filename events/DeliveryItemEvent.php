<?php

namespace yiicod\mailqueue\events;

use yii\base\Event;
use yiicod\mailqueue\models\MailRepositoryInterface;

class DeliveryItemEvent extends Event
{
    /**
     * @var MailRepositoryInterface
     */
    public $model;

    public function __construct(MailRepositoryInterface $model, array $config = [])
    {
        $this->model = $model;
        parent::__construct($config);
    }
}
