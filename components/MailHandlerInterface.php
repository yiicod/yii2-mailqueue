<?php

namespace yiicod\mailqueue\components;

use yiicod\mailqueue\models\MailRepositoryInterface;

interface MailHandlerInterface
{
    /**
     * Find mails
     *
     * @return mixed
     */
    public function findAll();

    /**
     * @param MailRepositoryInterface $item
     *
     * @return bool
     */
    public function send(MailRepositoryInterface $item): bool;

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function success(array $ids);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function failed(array $ids);
}
