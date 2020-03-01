<?php

namespace App\System\Filesystem;

abstract class File
{
    /**
     * @param string $path
     * @return bool
     *
     * Проверка существования файла
     */

    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @param string $path
     * @return bool|string
     *
     * Загрузить контент из файла, если существует файл
     */

    public static function get(string $path): string
    {
        return static::exists($path) ? file_get_contents($path) : '';
    }

    /**
     * Записать данные в файл
     *
     * @param string $path
     * @param string|null $data
     * @return bool|int
     */

    public static function put(string $path, ?string $data)
    {
        return file_put_contents($path, $data, LOCK_EX);
    }

    /**
     * Записать данные в конец файла (не перезатирать)
     *
     * @param string $path
     * @param string|null $data
     * @return bool|int
     */

    public static function append(string $path, ?string $data = '')
    {
        return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
    }

    /**
     * Удалить файл
     *
     * @param string $path
     */

    public static function delete(string $path)
    {
        if(static::exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Получить формат файла
     *
     * @param string $path
     * @return mixed
     */

    public static function extension(string $path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Получить тип
     *
     * @param string $path
     * @return false|string
     */

    public static function type(string $path)
    {
        return filetype($path);
    }

    /**
     * Получить размер файла (в байтах)
     *
     * @param string $path
     * @return false|int
     */

    public static function size(string $path)
    {
        return filesize($path);
    }

    /**
     * @param string $path
     * @return false|int
     */

    public static function modified(string $path)
    {
        return filemtime($path);
    }

    /**
     * @param string $path
     * @param string $target
     * @return bool
     */
    public static function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Переименовать файл
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public static function rename(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Путь к корню директории проекта
     */

    public static function root(): string
    {
        return dirname(dirname(dirname(__DIR__)));
    }
}