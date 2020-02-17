<?php

if (!function_exists('collect')) {
    function collect(array $collection = []): \App\System\Collection
    {
        return new \App\System\Collection($collection);
    }
}

if (!function_exists('class_basename'))
{
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('dd'))
{
    function dd(...$data)
    {
        echo sprintf('<pre style="color: %s">', 'black');
            var_dump(...$data);
        echo '</pre>';
        die;
    }
}