<?php

use yii\db\Migration;

/**
 * Class m201012_132245_tbl_u_control
 */
class m201012_132245_tbl_u_control extends Migration
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
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->createIndex('u_control_user_id', self::TABLE_CONTROL, 'user_id');
        $this->createIndex('u_control_remote_ip', self::TABLE_CONTROL, 'remote_ip');

    }


    public function safeDown()
    {
        $this->dropIndex('u_control_user_id', self::TABLE_CONTROL);
        $this->dropIndex('u_control_remote_ip', self::TABLE_CONTROL);
        $this->dropTable(self::TABLE_CONTROL);

        return true;
    }
}
