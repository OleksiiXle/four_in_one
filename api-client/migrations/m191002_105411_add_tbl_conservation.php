<?php

use yii\db\Migration;

/**
 * Class m191002_105411_add_tbl_conservation
 */
class m191002_105411_add_tbl_conservation extends Migration
{
    const TABLE_NAME = '{{%conservation}}';
    const TABLE_NAME2 = '{{%user}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_NAME, [
            'user_id' => $this->primaryKey()->comment('Идентификатор'),
            'conservation' => $this->text(),
        ], $tableOptions);

        $this->addForeignKey('fk_user_conservation', self::TABLE_NAME,'user_id',
            self::TABLE_NAME2, 'id', 'cascade', 'cascade');

    }


    public function safeDown()
    {
        $this->dropForeignKey('fk_user_conservation', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }

}