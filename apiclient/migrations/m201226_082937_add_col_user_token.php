<?php

use yii\db\Migration;

/**
 * Class m201226_082937_add_col_user_token
 */
class m201226_082937_add_col_user_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_token}}', 'provider_id', $this->string(50)->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user_token}}', 'provider_id');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201226_082937_add_col_user_token cannot be reverted.\n";

        return false;
    }
    */
}
