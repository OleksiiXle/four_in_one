<?php

use yii\db\Migration;

/**
 * Class m191002_103544_add_tbl_user
 */
class m191002_103544_add_tbl_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string(255)->notNull()->unique(),
            'email_confirm_token' => $this->string()->unique(),
            'verification_token' => $this->string()->defaultValue(null)->unique(),
            'refresh_permissions' => $this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->defaultValue(null),
            'updated_by' => $this->integer()->defaultValue(null),
        ], $tableOptions);
        $this->createIndex('user_username', '{{%user}}', 'username', true);
        $this->createIndex('user_email', '{{%user}}', 'email', true);

    }

    public function down()
    {
        $this->dropIndex('user_username', '{{%user}}');
        $this->dropIndex('user_email', '{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
