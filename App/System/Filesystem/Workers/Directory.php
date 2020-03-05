<?php

namespace App\System\Filesystem\Workers;

use App\System\Collection;

class Directory
{
    /**
     * @var \SplFileInfo $dirInfo
     */
    protected $dirInfo;
    /**
     * @var array $files
     */
    protected $files = [];

    public function __construct(string $path)
    {
        /**
         * @var string $path
         */
        $path = preg_replace('/\/+/', DIRECTORY_SEPARATOR, $path);

        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        try {
            $this->dirInfo = new \DirectoryIterator($path);
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @param string $mask
     * @param bool $searchInnerDirectories
     * @return Collection
     * @throws \Exception
     */
    public function files($mask = '*', bool $searchInnerDirectories = true): Collection
    {
        /**
         * @var array $result
         */
        $result = [];
        /**
         * вложенные директории
         * @var array $directories
         */
        $directories = $this->directories($searchInnerDirectories)->all();

        $files = new \FilesystemIterator($this->path(), \FilesystemIterator::SKIP_DOTS);

        if(is_array($mask)) {
            $mask = implode(',', $mask);
        }

        foreach ($files as $file) {
            if($file->isDir()) {
                continue;
            } else {
                if(file_exists($file->getRealPath())) {
                    // если маска - строка *
                    // то возвращаем ВСЕ файлы
                    if($mask === '*') {
                        $result[] = new File($file->getRealPath());
                        // иначе проверяем расширение файла
                    } elseif (strpos($mask, strtolower($file->getExtension())) !== false) {
                        $result[] = new File($file->getRealPath());
                    }
                }
            }
        }
        // $searchInnerDirectories - это условие только для корневой папки
        // остальные (вложенные) папки не должны дублировать файлы
        if($searchInnerDirectories) {
            foreach ($directories as $directory) {
                $innerFiles = $directory->files($mask, false)->all();

                if(count($innerFiles) > 0) {
                    $result = array_merge($result, $innerFiles);
                }
            }
        }
        /**
         * @var Collection $files
         */
        $this->files = collect($result);

        return $this->files;
    }

    /**
     * Создает файл внутри текущей директории
     * @param string $fileName
     * @return File
     * @throws \Exception
     */
    public function createFile(string $fileName): File
    {
        return File::create($this->path() . $fileName);
    }

    /**
     * Создает рекурсивно папку (если имя указано, например, "foo/bar/baz") внутри текущей директории
     * @param string $directoryName
     * @return Directory
     */
    public function createDirectory(string $directoryName): Directory
    {
        return new static($this->path() . $directoryName);
    }

    /**
     * @param bool $preserve
     * @return \App\System\Filesystem\Workers\Directory
     * @throws \Exception
     */
    public function delete(bool $preserve = false): Directory
    {
        if($this->isDir()) {
            /**
             * @var Collection $files
             */
            $files = $this->files();
            // сперва удаляем все файлы
            if($files && $files->count() > 0) {
                $files->each(function (File $file) {
                    $file->delete();
                });

                unset($this->files);
                unset($files);
            } else {
                unset($this->files);
                unset($files);
            }
            // после этого рекурсивно удаляем все папки
            $this->recursiveDeleteDirectory($this->path(), $preserve);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDir(): bool
    {
        return is_dir($this->dirInfo->getRealPath());
    }

    /**
     * @param string $path
     * @return Directory
     */
    public static function open(string $path): Directory
    {
        return new static($path);
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->dirInfo->getRealPath() . DIRECTORY_SEPARATOR;
    }

    public function all(): array
    {
        return $this->files;
    }
    /**
     * @return Collection
     */
    public function directories(): Collection
    {
        /**
         * @var array $result
         */
        $result = [];
        /**
         * @var array $directories
         */
        $directories = glob($this->dirInfo->getRealPath() . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

        if($directories) {
            foreach ($directories as $directory) {
                if(is_dir($directory)) {
                    /**
                     * @var Directory $directory
                     */
                    $directory = new Directory($directory);

                    /**
                     * @var array $innerDirectories
                     */
                    $innerDirectories = $directory->directories()->all();
                    // если есть вложенные директории
                    // то добавляем текущую директорию и вложенные
                    if($innerDirectories) {
                        $result[] = $directory;

                        $result = array_merge($result, $innerDirectories);
                    } else { // иначе - только текущую директорию
                        $result[] = $directory;
                    }

                    continue;
                }
            }
        }

        return collect($result);
    }

    /**
     * Получить дочернюю директорию по имени
     *
     * @param string $name
     * @return Directory|null
     */
    public function directory(string $name): ?Directory
    {
        return $this->directories()->filter(function (Directory $directory) use ($name) {
            return $directory->name() === $name;
        })->first();
    }

    /**
     * получить имя папки
     * @return string
     */
    public function name(): string
    {
        /**
         * @var array $dirinfo
         */
        $dirinfo = pathinfo($this->dirInfo->getRealPath());

        return $dirinfo['basename'];
    }

    /**
     * @param string $name
     * @param bool $withoutExtension
     * @return File|null
     * @throws \Exception
     */
    public function file(string $name, bool $withoutExtension = false): ?File
    {
        return $this->files()->filter(function (File $file) use ($name, $withoutExtension) {
            return $file->name($withoutExtension) === $name;
        })->first();
    }

    /**
     * @param string $path
     * @param bool $preserve
     */
    protected function recursiveDeleteDirectory(string $path, bool $preserve = false)
    {
        /**
         * @var array $directories
         */
        $directories = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            if(is_dir($directory)) {
                $this->recursiveDeleteDirectory($directory);
            }
        }

        if(!$preserve) @rmdir($path);
    }
}