<?php

use yii\db\Migration;

/**
 * Class m201016_081505_tbl_background_task
 */
class m201016_081505_tbl_background_task extends Migration
{
    const TABLE_NAME = '{{%background_task}}';

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
            'pid' => $this->integer(11),
            'user_id' => $this->integer(11),
            'model' => $this->string(255)->notNull(),
            'arguments' => $this->text()->defaultValue(null),
            'time_limit' => $this->integer(11)->defaultValue(3600),
            'status' => $this->string(10)->defaultValue('new'),
            'custom_status' => $this->string(100)->defaultValue(null),
            'result_file' => $this->string(256)->defaultValue(null),
            'result_file_pointer' => $this->integer(11)->defaultValue(0),
            'progress' => $this->integer(11)->defaultValue(0),
            'result' => $this->text()->defaultValue(null),
            'datetime_create' => $this->dateTime(),
            'datetime_update' => $this->dateTime(),
        ], $tableOptions);
        $this->createIndex('idx_background_tasks_status', self::TABLE_NAME, 'status');
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
