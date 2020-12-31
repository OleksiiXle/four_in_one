Клиентское приложение

composer update

1. mysql -u root -p
CREATE DATABASE xle_client;
EXIT;

добавить имена баз данных и пароли в config

2. php yii migrate

3. Наполнение баз данных тестовыми данными
   php yii init/add-data
   (если надо очистить предварительно таблицы - php yii init/remove-data)

По отдельности (при необходимости):
   php yii adminxx/common-roles-init
   php yii adminxx/menu-init
   php yii adminxx/users-init
   php yii translate/init
   php yii provider/init

4 Создание администраторов (при необходимости, по выбору)
   php yii adminxx/make-admin

5 Создание суперадмина (при необходимости)
   php yii adminxx/make-super-admin

composer update

chmod -R 777 /var/www/html/dstest/apiadmin/runtime
chmod -R 777 /var/www/html/dstest/apiadmin/web/assets
chmod -R 777 /var/www/html/dstest/apiuser/runtime
chmod -R 777 /var/www/html/dstest/apiuser/web/assets
chmod -R 777 /var/www/html/dstest/apiclient/web/assets
chmod -R 777 /var/www/html/dstest/apiclient/runtime
chmod -R 777 /var/www/html/dstest/apiserver/web/assets
chmod -R 777 /var/www/html/dstest/apiserver/runtime
mkdir /var/www/html/dstest/common/runtime/logs
chmod -R 777 /var/www/html/dstest/common/runtime
