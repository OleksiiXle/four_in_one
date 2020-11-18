<?php

use yii\db\Migration;

/**
 * Class m201015_105549_tbl_conservation
 */
class m201015_105549_tbl_conservation extends Migration
{
    const TABLE_NAME = '{{%conservation}}';
    const TABLE_NAME_PARENT = '{{%user}}';

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
            // 'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull()->comment('user id')->unique(),
            'conservation' => $this->text()->notNull()->comment('conservation'),
        ], $tableOptions);

        $this->createIndex('conservation_user_id', self::TABLE_NAME, 'user_id');
        $this->addForeignKey('fk_user_conservation_id', self::TABLE_NAME,'user_id',
            self::TABLE_NAME_PARENT, 'id', 'cascade', 'cascade');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_conservation_id', self::TABLE_NAME);
        $this->dropIndex('user_id', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);

        return true;
    }
}