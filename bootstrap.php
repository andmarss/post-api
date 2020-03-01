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

if (!function_exists('root'))
{
    /**
     * @param string|null $path
     * @return string
     */
    function root(string $path = null): string
    {
        return is_null($path) && empty($path)
            ? \App\System\Filesystem\File::root()
            : sprintf('%s%s', \App\System\Filesystem\File::root(), str_replace('/', DIRECTORY_SEPARATOR, $path));
    }
}

if (!function_exists('config'))
{
    /**
     * @param string $configPath
     * @return mixed|null
     */
    function config(string $configPath)
    {
        return \App\System\Config::get($configPath);
    }
}

if (!function_exists('slug'))
{
    /**
     * Кирилицу в латиницу
     * @param string $value
     * @return string
     */
    function slug(string $value): string
    {
        $cyrillic = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];

        $latin = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];

        return strtolower(preg_replace('/[\s]+/', '-', str_replace($cyrillic, $latin, $value)));
    }
}

if (!function_exists('factory'))
{
    /**
     * @param string $class
     * @param int $num
     * @return \App\System\Factory\Factory
     */
    function factory(string $class, int $num = 1): \App\System\Factory\Factory
    {
        return new App\System\Factory\Factory($class, $num);
    }
}