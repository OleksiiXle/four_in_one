<?php

use yii\db\Migration;

/**
 * Class m201205_092225_tbl_oauthclient
 */
class m201205_092225_tbl_oauthclient extends Migration
{
    const TABLE_NAME = '{{%provider}}';

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
            'name' => $this->string(50)->notNull(),
            'class' => $this->string(256)->notNull(),
            'client_id' => $this->string(256)->defaultValue(null),
            'client_secret' => $this->string(256)->defaultValue(null),
            'token_url' => $this->string(500)->defaultValue(null),
            'auth_url' => $this->string(500)->defaultValue(null),
            'signup_url' => $this->string(500)->defaultValue(null),
            'api_base_url' => $this->string(500)->defaultValue(null),
            'scope' => $this->text(),
            'state_storage_class' => $this->string(256)->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->defaultValue(null),
            'updated_by' => $this->integer()->defaultValue(null),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);

        return true;
    }
}
