<?php

use yii\db\Migration;

/**
 * Class m201012_123211_tbl_user
 */
class m201012_123211_tbl_user extends Migration
{
    const TABLE_NAME = '{{%user}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'username' => $this->string(32)->notNull()->unique()->comment('Login'),
            'auth_key' => $this->string(32)->notNull()->comment('AuthKey'),
            'password_hash' => $this->string(255)->notNull()->comment('password hash'),
            'password_reset_token' => $this->string(255)->null()->comment('password reset token'),
            'email' => $this->string(255)->null()->unique()->comment('email'),
            'status' => $this->integer(2)->defaultValue(10)->notNull()->comment('status'),
            'refresh_permissions' => $this->integer(1)->defaultValue(0)->comment('refresh permissions'),
            'created_at' => $this->integer(11)->notNull()->comment('created at'),
            'updated_at' => $this->integer(11)->comment('updated at'),
            'created_by' => $this->integer(11)->defaultValue(0)->comment('created by'),
            'updated_by' => $this->integer(11)->defaultValue(0)->comment('updated by'),
        ], $tableOptions);

        $this->createIndex('user_username', self::TABLE_NAME, 'username');
        $this->createIndex('user_email', self::TABLE_NAME, 'email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('user_email', self::TABLE_NAME);
        $this->dropIndex('user_username', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);

        return true;
    }
}
