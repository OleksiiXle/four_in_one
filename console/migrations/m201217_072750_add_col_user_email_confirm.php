<?php

use yii\db\Migration;

/**
 * Class m201217_072750_add_col_user_email_confirm
 */
class m201217_072750_add_col_user_email_confirm extends Migration
{
    const TABLE_NAME = '{{%user}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'email_confirm_token', $this->string(255)->unique());
        $this->addColumn(self::TABLE_NAME, 'verification_token', $this->string(255)->defaultValue(null)->unique());
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'email_confirm_token');
        $this->dropColumn(self::TABLE_NAME, 'verification_token');

        return true;
    }
}
