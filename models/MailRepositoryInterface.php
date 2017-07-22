<?php

namespace yiicod\mailqueue\models;

//$to, $subject, $body, $priority = 0, $from = '', array $attaches = [],
interface MailRepositoryInterface
{
    /**
     * Get data for setAttributes
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Push to storage mail
     *
     * @return bool
     */
    public function push(): bool;
}
