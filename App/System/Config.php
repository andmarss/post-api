<?php


namespace App\System;


class Config
{
    protected static $config;

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if(is_null(static::$config)) {
            static::load();
        }

        $config = static::$config;

        if (count($config) === 0) return false;

        $keys = explode('/', $key);

        foreach ($keys as $key) {
            if (!isset($config[$key])) return false;

            $config = $config[$key];

            continue;
        }

        return true;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed|null
     */
    public static function get(string $key, ?string $default = null)
    {
        if(is_null(static::$config)) {
            static::load();
        }

        if(!static::$config || !static::has($key)) return $default;

        $config = static::$config;

        $keys = explode('/', $key);

        foreach ($keys as $key) {
            $config = isset($config[$key]) && $config ? $config[$key] : false;
        }

        return $config;
    }

    /**
     * Загрузить файл конфигурации
     */
    protected static function load(): void
    {
        $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config.php';

        if(file_exists($file)) {
            static::$config = (require $file);
        } else {
            static::$config = [];
        }
    }
}