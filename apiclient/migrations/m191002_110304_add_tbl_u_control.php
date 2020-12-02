<?php

use yii\db\Migration;

/**
 * Class m191002_110304_add_tbl_u_control
 */
class m191002_110304_add_tbl_u_control extends Migration
{
    const TABLE_CONTROL = '{{%u_control}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_CONTROL, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->defaultValue(null),
            'remote_ip' => $this->string(32)->defaultValue(null),
            'referrer' => $this->text()->defaultValue(null),
            'remote_host' => $this->string(32)->defaultValue(null),
            'absolute_url' => $this->text()->defaultValue(null),
            'url' => $this->text()->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }


    public function safeDown()
    {
        $this->dropTable(self::TABLE_CONTROL);
    }

}