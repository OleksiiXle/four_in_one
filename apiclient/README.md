Клиентское приложение

composer update

mysql -u root -p
CREATE DATABASE xle_client;
EXIT;

php yii migrate

1. Добавить недостающие разрешения и роли (из консоли)
   php yii adminxx/common-roles-init

2. Инициализировать новое меню (из консоли), старое - останется.
   php yii adminxx/menu-init
   
3. Инициализировать дефолтных пользователей.
   php yii adminxx/users-init

4 Сщздание администраторов (по выбору)
   php yii adminxx/make-admin

5 Создание суперадмина
   php yii adminxx/make-super-admin
   
6. Инициализация словаря
   php yii translate/init
   
   php yii provider/init

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
