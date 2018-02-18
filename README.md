Yii Mail Queue
==============

[![Latest Stable Version](https://poser.pugx.org/yiicod/yii2-mailqueue/v/stable)](https://packagist.org/packages/yiicod/yii2-mailqueue) [![Total Downloads](https://poser.pugx.org/yiicod/yii2-mailqueue/downloads)](https://packagist.org/packages/yiicod/yii2-mailqueue) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiicod/yii2-mailqueue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiicod/yii2-mailqueue/?branch=master)[![Code Climate](https://codeclimate.com/github/yiicod/yii2-mailqueue/badges/gpa.svg)](https://codeclimate.com/github/yiicod/yii2-mailqueue)

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
php yii migrate/up --migrationPath=@vendor/yiicod/yii2-mailqueue/migrations
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

#### Config

Use pm2(http://pm2.keymetrics.io/) as daemons manager.
```php
'components' => [
    'mailqueue' => [
        'class' => 'yiicod\mailqueue\WorkerCommand',
    ],
]
...
'bootstrap' => array('mailqueue')
```

Full config you can find in the yiicod\mailqueue\config.

#### Console command

```php
'commandMap' => [
    'mail-queue' => [
        'class' => 'yiicod\mailqueue\commands\MailQueueCommand',
    ],
],
```
##### OR use pm2(http://pm2.keymetrics.io/). This variant more preferable.

```php
'commandMap' => [
    'mail-queue' => [
        'class' => 'yiicod\mailqueue\commands\WorkerCommand',
    ],
],
```
###### pm2 config:
```json
    {
      "apps": [
        {
          "name": "job-queue",
          "script": "yii",
          "args": [
            "mailqueue/work"
          ],
          "exec_interpreter": "php",
          "exec_mode": "fork_mode",
          "max_memory_restart": "1G",
          "watch": false,
          "merge_logs": true,
          "out_file": "runtime/logs/job_queue.log",
          "error_file": "runtime/logs/job_queue.log"
        }
      ]
    }
```
###### Run PM2 daemons
```bash
pm2 start daemons-app.json
```

#### Migration usage
Migration command or use manual(http://www.yiiframework.com/doc-2.0/guide-db-migrations.html) for configuration:
```php
   yii migrate --migrationPath=@yiicod/mailqueue/migrations
```

#### Push in queue

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