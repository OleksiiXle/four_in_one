<?php

use yii\db\Migration;

/**
 * Class m191015_064802_add_tbl_auth_user_api
 */
class m191015_064802_add_tbl_auth_user_api extends Migration
{
    public function up()
    {
        $this->createTable('user_api', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'auth_key' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('auth', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'source' => $this->string()->notNull(),
            'source_id' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk-auth-user_id-user-id', 'auth', 'user_id', 'user_api', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('auth');
        $this->dropTable('user_api');
    }}
