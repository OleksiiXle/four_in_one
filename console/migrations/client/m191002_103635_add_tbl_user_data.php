<?php

use yii\db\Migration;

/**
 * Class m191002_103635_add_tbl_user_data
 */
class m191002_103635_add_tbl_user_data extends Migration
{
    const TABLE_USER = '{{%user}}';
    const TABLE_USER_DATA = '{{%user_data}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_USER_DATA, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'first_name' => $this->string(50)->defaultValue('')->comment('Имя'),
            'middle_name' => $this->string(50)->defaultValue('')->comment('Отчество'),
            'last_name' => $this->string(50)->defaultValue('')->comment('Фамилия'),
            'phone' => $this->string(50)->defaultValue('')->comment('phone'),
            'emails' => $this->text()->defaultValue(null)->comment('emails'),
            'profile' => $this->text()->defaultValue(null)->comment('profile'),
            'last_rout' => $this->string(255)->comment('last rout'),
            'last_rout_time' => $this->dateTime()->comment('last rout time'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->defaultValue(null),
            'updated_by' => $this->integer()->defaultValue(null),
        ], $tableOptions);


        $this->addForeignKey('fk_user_user_data', self::TABLE_USER_DATA,'user_id',
            self::TABLE_USER, 'id', 'cascade', 'cascade');
    }


    public function safeDown()
    {
        $this->dropForeignKey('fk_user_user_data', self::TABLE_USER_DATA);
        $this->dropTable(self::TABLE_USER_DATA);
    }

}