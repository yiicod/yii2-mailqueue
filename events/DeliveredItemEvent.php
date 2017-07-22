<?php

namespace yiicod\mailqueue\events;

use yii\base\Event;
use yiicod\mailqueue\models\MailRepositoryInterface;

class DeliveredItemEvent extends Event
{
    /**
     * @var bool
     */
    public $isSuccess;

    /**
     * @var MailRepositoryInterface
     */
    public $model;

    /**
     * DeliveredItemEvent constructor.
     *
     * @param bool $isSuccess
     * @param array $model
     * @param array $config
     */
    public function __construct(bool $isSuccess, MailRepositoryInterface $model, array $config = [])
    {
        $this->isSuccess = $isSuccess;
        $this->model = $model;
        parent::__construct($config);
    }
}
