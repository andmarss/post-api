<?php

if (!function_exists('collect')) {
    /**
     * @param array $collection
     * @return \App\System\Collection
     */
    function collect(array $collection = []): \App\System\Collection
    {
        return new \App\System\Collection($collection);
    }
}

if (!function_exists('class_basename'))
{
    /**
     * @param $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('dd'))
{
    /**
     * @param mixed ...$data
     */
    function dd(...$data)
    {
        echo sprintf('<pre style="color: %s">', 'black');
            var_dump(...$data);
        echo '</pre>';
        die;
    }
}

if (!function_exists('response'))
{
    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return \App\System\Response
     */
    function response(string $content = '', int $status = 200, array $headers = []): \App\System\Response
    {
        return new \App\System\Response($content, $status, $headers);
    }
}