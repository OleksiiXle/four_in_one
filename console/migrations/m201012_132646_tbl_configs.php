<?php

use yii\db\Migration;

/**
 * Class m201012_132646_tbl_configs
 */
class m201012_132646_tbl_configs extends Migration
{
    const TABLE_ = '{{%configs}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_, [
            'id' => $this->primaryKey(),
            'owner' => $this->string(255)->notNull(),
            'name' => $this->string(250)->notNull()->unique(),
            'content' => $this->text()->defaultValue(null),
            'created_at' => $this->integer(11)->notNull()->comment('created at'),
            'updated_at' => $this->integer(11)->notNull()->comment('updated at'),
            'created_by' => $this->integer(11)->defaultValue(0)->comment('created by'),
            'updated_by' => $this->integer(11)->defaultValue(0)->comment('updated by'),
        ], $tableOptions);

    }


    public function safeDown()
    {
        $this->dropTable(self::TABLE_);

        return true;
    }

}
