<?php

namespace yiicod\mailqueue\events;

use yii\base\Event;

class DeliveredEvent extends Event
{
    /**
     * @var array
     */
    public $successIds;

    /**
     * @var array
     */
    public $failedIds;

    /**
     * DeliveredEvent constructor.
     *
     * @param array $successIds
     * @param array $failedIds
     * @param array $config
     */
    public function __construct(array $successIds, array $failedIds, array $config = [])
    {
        $this->successIds = $successIds;
        $this->failedIds = $failedIds;
        parent::__construct($config);
    }
}
