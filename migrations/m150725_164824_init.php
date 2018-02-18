<?php

use yii\db\Migration;
use yii\db\Schema;

class m150725_164824_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ('mysql' === $this->db->driverName) {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%mail_queue}}', [
            'id' => Schema::TYPE_PK,
            'from' => Schema::TYPE_STRING . '(100) NOT NULL',
            'to' => Schema::TYPE_STRING . '(100) NOT NULL',
            'mailer' => Schema::TYPE_STRING . '(100) DEFAULT NULL',
            'subject' => Schema::TYPE_STRING . '(100) NOT NULL',
            'body' => Schema::TYPE_TEXT . ' NOT NULL',
            'attaches' => Schema::TYPE_TEXT . ' NOT NULL',
            'priority' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 0',
            'status' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 0',
            'created_date' => Schema::TYPE_DATETIME . ' NOT NULL',
            'updated_date' => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%MailQueue}}');
    }
}
