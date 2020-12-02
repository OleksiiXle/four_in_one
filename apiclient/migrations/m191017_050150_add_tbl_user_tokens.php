<?php

use yii\db\Migration;

/**
 * Class m191017_050150_add_tbl_user_tokens
 */
class m191017_050150_add_tbl_user_tokens extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        /*
         yii\authclient\OAuthToken::__set_state(array(
   'tokenParamKey' => 'access_token',
   'tokenSecretParamKey' => 'oauth_token_secret',
   'createTimestamp' => 1571289341,
   '_expireDurationParamKey' => NULL,
   '_params' =>
  array (
    'access_token' => 'j83y2AU2keqGeSUf96wHuf1dbIpxYEzAVxZp2gB7',
    'expires_in' => 3600,
    'token_type' => 'bearer',
    'scope' => NULL,
    'refresh_token' => 'OY_0udD9fkNZNHiuiVM2oSuZkMNPIS3GBzcdtUjr',
  ),
))
         */

        $this->createTable('{{%user_token}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer(11)->notNull()->comment('Пользователь CMS'),
            'api_id' => $this->integer(11)->notNull()->comment('Пользователь API'),
            'provider' => $this->string(50)->notNull()->comment('Провайдер'),
            'tokenParamKey' => $this->string(255)->notNull()->comment(''),
            'tokenSecretParamKey' => $this->string(255)->notNull()->comment(''),
            'access_token' => $this->string(255)->notNull()->comment(''),
            'expires_in' => $this->string(255)->notNull()->comment(''),
            'token_type' => $this->string(255)->notNull()->comment(''),
            'scope' => $this->string(255)->defaultValue(null)->comment(''),
            'refresh_token' => $this->string(255)->notNull()->comment(''),
            'permissions' => $this->text()->defaultValue(null)->comment(''),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_user_user_token', '{{%user_token}}','client_id',
            '{{%user}}', 'id', 'cascade', 'cascade');

        $this->createIndex('client_id_api_id_provider', '{{%user_token}}',
            ['client_id', 'api_id', 'provider' ], true);

    }

    public function down()
    {
        $this->dropForeignKey('fk_user_user_token', '{{%user_token}}');
        $this->dropIndex('client_id_api_id_provider', '{{%user_token}}');
        $this->dropTable('{{%user_token}}');
    }
}
