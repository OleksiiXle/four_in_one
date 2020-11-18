<?php

use yii\db\Migration;

/**
 * Class m201101_070103_add_col_user_data
 */
class m201101_070103_add_col_user_data extends Migration
{
    const TABLE_NAME = '{{%user_data}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'profile', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'profile');

        return true;
    }
}
