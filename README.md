Mail Queue
==========
Mail queue for emails. You don't need think how many emails will be send, because 
when you install this extension you can setting this. You will have table, where you 
can see emails status.

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiicod/yii2-mailqueue "*"
```

or add

```json
"yiicod/yii2-mailqueue": "*"
```

to the require section of your composer.json.

run
```php
php yii migrate/up --migrationPath=@yiicod/mailqueue/migrations
```
Please note that messages are wrapped with ```Yii::t()``` to support message translations, you should define default message source for them if you don't use i18n.
```php
'i18n' => [
    'translations' => [
        '*' => [
            'class' => 'yii\i18n\PhpMessageSource'
        ],
    ],
],

Config
---------------------------------------------

Configure your cron for console command or you can see in file
--------------------------------------------------------------
../mailqueue/components/MailQueue.php

Add component in main.php
-------------------------

Lite config
-----------

```php
'components' => array(
    ...
        'mailqueue' => array(
            'class' => 'yiicod\mailqueue\MailQueue',
        ),
    ...    
)
...
'preload' => array('mailqueue')
```

Full config
-----------
```php
'components' => array(
    ...
        'mailqueue' => array(
            'class' => 'yiicod\mailqueue\MailQueue',
            'modelMap' => array(
                'MailQueue' => array(
                    'alias' => 'yiicod\mailqueue\models\MailQueueModel',
                    'class' => 'yiicod\mailqueue\models\MailQueueModel',
                    'fieldFrom' => 'from',
                    'fieldTo' => 'to',
                    'fieldSubject' => 'subject',
                    'fieldBody' => 'body',
                    'fieldAttachs' => 'attachs',
                    'fieldStatus' => 'status',
                    'status' => array(
                        'send' => 1,
                        'unsend' => 0,
                        'failed' => 0,
                    )
                )
            ),
            'mailer' => 'mailer',
            'components' => array(
                'mailQueue' => array(
                    'class' => 'yiicod\mailqueue\components\MailQueue',
                    'afterSendDelete' => false,
                ),
            ),
        ),
    ...
)
...
'preload' => array('mailqueue')
```

Config for mail Queue console command
-------------------------------------

```php
'commandMap' => array(
    'mailQueue' => array(
        'class' => 'yiicod\mailqueue\commands\MailQueueCommand',
        'limit' => 60,
        'condition' => 'status=:unsend OR status=:failed',
        'params' => array(':unsend' => 0, ':failed' => 0),
    ),
),
```

Using
-----

Push in queue
-------------

```php
/**
 * Add mail from queue
 * @param string $to Email to
 * @param string $subject Email subject
 * @param string $body email, html
 * @param string|Array $from From email
 * @param string $attachs Attach for email array('path' => 'file path', 'name' => 'file bname')
 * @param Array $additionalFields Any additional fields
 */
Yii::app()->mailQueue->push($to, $subject, $body, $from = '', array $attachs = [], $additionalFields = []);
```
OR
```php
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
Yii::app()->mailQueue->pushMass($data)
```

You can use method deliveryBegin and deliveryEnd for send mass mails to mail server:

```php

class SmartMailer extends <component>
{

    /**
     *
     * @var type 
     */
    private $deliveryQueue = array();

    /**
     * Start delivery emails
     * @param array $models Array of models
     * @author Orlov Alexey <aaorlov88@gmail.com>
     * @since 1.0
     */
    public function deliveryBegin($models)
    {
        
    }

    /**
     * Send mail
     * @param string $to Email to
     * @param string $subject Email subject
     * @param string Body email, html
     * @param string|Array From email
     * @param string Attach for email array('path' => 'file path', 'name' => 'file bname')
     * @author Orlov Alexey <aaorlov88@gmail.com>
     * @since 1.0
     */
    public function send($to, $subject, $message, $from = '', array $attachs = array())
    {
        $this->deliveryQueue[] = array(
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'from' => $from,
            'attachs' => $attachs
        );
        return true;
    }

    /**
     * End delivery emails
     * @param array $successIds
     * @param array $failedIds
     * @author Orlov Alexey <aaorlov88@gmail.com>
     * @since 1.0
     */
    public function deliveryEnd(&$successIds, &$failedIds)
    {
        if (false === $this->sendMass($this->deliveryQueue)) {
            $failedIds = $successIds;
        }
        $this->deliveryQueue = [];
    }

    public function sendMass($messages){
        //send to service for example
    }
}
```