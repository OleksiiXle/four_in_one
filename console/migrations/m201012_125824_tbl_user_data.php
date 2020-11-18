<?php

use yii\db\Migration;

/**
 * Class m201012_125824_tbl_user_data
 */
class m201012_125824_tbl_user_data extends Migration
{
    const TABLE_NAME = '{{%user_data}}';
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
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull()->comment('user id'),
            'first_name' => $this->string(50)->notNull()->comment('first name'),
            'middle_name' => $this->string(50)->comment('first name'),
            'last_name' => $this->string(50)->notNull()->comment('last name'),
            'emails' => $this->text()->comment('emails'),
            'phone' => $this->string(255)->comment('phone'),
            'last_rout' => $this->string(255)->comment('last rout'),
            'last_rout_time' => $this->dateTime()->comment('last rout time'),
            'created_at' => $this->integer(11)->notNull()->comment('created at'),
            'updated_at' => $this->integer(11)->notNull()->comment('updated at'),
            'created_by' => $this->integer(11)->defaultValue(0)->comment('created by'),
            'updated_by' => $this->integer(11)->defaultValue(0)->comment('updated by'),
        ], $tableOptions);

        $this->createIndex('user_data_user_id', self::TABLE_NAME, 'user_id');

        $this->addForeignKey('fk_user_user_data_id', self::TABLE_NAME,'user_id',
            self::TABLE_NAME_PARENT, 'id', 'cascade', 'cascade');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_user_data_id', self::TABLE_NAME);
        $this->dropIndex('user_id', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);

        return true;
    }
}
