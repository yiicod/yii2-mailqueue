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
```

Base config
-----------

```php
'components' => [
    'mailqueue' => [
        'class' => 'yiicod\mailqueue\MailQueue',
    ],
]
...
'bootstrap' => array('mailqueue')
```
Full config you can find in the yiicod\mailqueue\config

Config for mail Queue console command
-------------------------------------

```php
'commandMap' => [
    'mail-queue' => [
        'class' => 'yiicod\mailqueue\commands\MailQueueCommand',
    ],
    'migrate' => [
        'migrationNamespaces' => [
            ...
            'yiicod_mailqueue_migrations',
            ...
        ],
        'migrationPath' => null
    ],
],
```

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
or
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