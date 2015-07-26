<?php

use yii\db\Migration;
use yii\db\Schema;

class m150725_164824_init extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%MailQueue}}', [
            'id' => Schema::TYPE_PK,
            'from' => Schema::TYPE_STRING . '(100) NOT NULL',
            'to' => Schema::TYPE_STRING . '(100) NOT NULL',
            'subject' => Schema::TYPE_STRING . '(100) NOT NULL',
            'body' => Schema::TYPE_TEXT . ' NOT NULL',
            'attachs' => Schema::TYPE_TEXT . ' NOT NULL',
            'priority' => Schema::TYPE_SMALLINT . '(2) NOT NULL',
            'status' => Schema::TYPE_SMALLINT . '(1) NOT NULL',
            'createdDate' => Schema::TYPE_DATETIME . ' NOT NULL',
            'updatedDate' => Schema::TYPE_DATETIME . ' NOT NULL',
                ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%MailQueue}}');
    }

}
