# post-api
для того, что бы заработало подключение к БД
нужно переименовать config.example.php в config.php

```php
 'connections' => [
          'database' => [
              'name'       => '', // название базы данных
              'username'   => '', // имя пользователя
              'password'   => '', // пароль для подключения к БД
              'connection' => '', // тип соединения: "mysql:host=123.4.5.6", "sqlite:example.db" ...etc
              'options'    => [   //
                  \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
              ]
          ]
      ]
```