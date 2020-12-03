Заготовка
Админка
Посты - редактирование без АПИ
Посты - сервер АПИ
Клиент АПИ:
    - админка клиента
    - доступ к постам по АПИ


.htaccess настроены так, что:
-hostName/ - (apiuser) фронтенд, работа с контентом сервера без АПИ
-hostName/admin - (apiadmin) бекенд, администрирование настроек и пользователей сервера без АПИ
-hostName/server - (api-server) АПИ - сервер, обработка запросов и выдача ответов клиенту по REST FULL API
-hostName/client - (apiclient) клиент АПИ - запросы к серверу на работу с контентом по REST FULL API


**************************************************************** 1-й вариант настройки хостинга (1 хост, 4 пути)
1. Хостинг
sudo gedit /etc/apache2/sites-available/staff.api.conf
<VirtualHost *:80>
    ServerName staff.api
    DocumentRoot /var/www/xle/staff-api
    <Directory /var/www/xle/staff-api>
        AllowOverride All
    </Directory>
</VirtualHost>

sudo gedit /etc/hosts
->   127.0.2.1       staff.api

sudo a2ensite staff.api.conf
sudo service apache2 restart

2. Файлы .htaccess -> .htaccess_IF_ONE_HOST

3. В конфигах всех частей:
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'vfyrHPxjQZfzATztb4_Lzlclxk0kcRLv',
            'baseUrl'=>'/apiserver', //todo раскомментировать в случае одного хоста
        ],


**************************************************************** 2-й вариант настройки хостинга (4 хостa)
sudo gedit /etc/apache2/sites-available/xle.admin.conf
<VirtualHost *:80>
    ServerName xle.admin
    DocumentRoot /var/www/xle/staff-api/apiadmin/web
    <Directory /var/www/xle/staff-api/apiadmin>
        AllowOverride All
    </Directory>
</VirtualHost>

sudo gedit /etc/apache2/sites-available/xle.user.conf
<VirtualHost *:80>
    ServerName xle.user
    DocumentRoot /var/www/xle/staff-api/apiuser/web
    <Directory /var/www/xle/staff-api/apiuser>
        AllowOverride All
    </Directory>
</VirtualHost>

sudo gedit /etc/apache2/sites-available/xle.api.server.conf
<VirtualHost *:80>
    ServerName xle.api.server
    DocumentRoot /var/www/xle/staff-api/apiserver/web
    <Directory /var/www/xle/staff-api/apiserver>
        AllowOverride All
    </Directory>
</VirtualHost>

sudo gedit /etc/apache2/sites-available/xle.api.client.conf
<VirtualHost *:80>
    ServerName xle.api.client
    DocumentRoot /var/www/xle/staff-api/apiclient/web
    <Directory /var/www/xle/staff-api/apiclient>
        AllowOverride All
    </Directory>
</VirtualHost>

sudo gedit /etc/hosts
->  
 127.0.2.1       xle.admin
 127.0.2.2       xle.user
 127.0.2.3       xle.api.server
 127.0.2.4       xle.api.client

sudo a2ensite xle.admin.conf
sudo a2ensite xle.user.conf
sudo a2ensite xle.api.server.conf
sudo a2ensite xle.api.client.conf

sudo a2ensite xle-admin.conf
sudo a2ensite xle-user.conf
sudo a2ensite xle-api-server.conf
sudo a2ensite xle-apiclient.conf

sudo a2dissite xle-admin.conf
sudo a2dissite xle-user.conf
sudo a2dissite xle-api-server.conf
sudo a2dissite xle-apiclient.conf

sudo service apache2 restart
*****************************************************************************************
Административная часть

1. Создание и настройка БД
mysql -u root -p
CREATE DATABASE test_api_server;
CREATE DATABASE test_api_client;
EXIT;

добавить в common/config/main.php

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

7. Добавление тестовых постов
   php yii post/init

8. Создание симлинков для фоновых задач 
    php yii init

mysql -u root -p
USE test_api_server;
INSERT INTO `oauth2_client`
(`client_id`, `client_secret`, `redirect_uri`, `grant_type`, `scope`, `created_at`, `updated_at`, `created_by`, `updated_by`)
VALUES ('xapi', '123','http://192.168.1.105/dstest/apiclient/site/login', 'UserCredentials','none',1,1,1,1);
EXIT;
    


Настройка клиента
см README в apiclient, 
в терминале войти в apiclient и там все запускать










php yii migrate-client --migrationPath=@console/migrations/client

1. Добавить недостающие разрешения и роли (из консоли)
   php yii adminxx-client/common-roles-init

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


CENTOS
RewriteEngine On

# End the processing, if a rewrite already occurred
RewriteRule ^(apiuser|admin|api|client)/web/ - [L]

# Handle the case of apiadmin, skip ([S=1]) the following rule, if current matched
RewriteRule ^admin(/(.*))?$ admin/web/$2 [S=1]

# Handle the case of apiadmin, skip ([S=1]) the following rule, if current matched
RewriteRule ^apiuser(/(.*))?$ apiuser/web/$2 [S=1]

# Handle the case of apiadmin, skip ([S=1]) the following rule, if current matched
RewriteRule ^api(/(.*))?$ api/web/$2 [S=1]

# Handle the case of apiadmin, skip ([S=1]) the following rule, if current matched
RewriteRule ^client(/(.*))?$ client/web/$2 [S=1]

# Uncomment the following, if you want speaking URL
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+/web)/.*$ $1/index.php