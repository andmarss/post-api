<?php

namespace App\Models;

use App\System\Collection;
use App\System\Database\DB;
use App\System\Database\QueryBuilder;
use App\Traits\MigrationTraits\UnderscoreAndCamelCaseTrait;
use App\Traits\Relations;

abstract class Model implements \JsonSerializable
{
    use Relations;
    use UnderscoreAndCamelCaseTrait;

    /**
     * @var string $table
     */
    protected $table;
    /**
     * @var string $primaryKey
     */
    protected $primaryKey = 'ID';
    /**
     * @var bool $exists
     */
    public $exists = false;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
    /**
     * @var array $fillable
     */
    protected $fillable = [];

    protected $dates = [];

    protected $data = [];
    /**
     * @var DB $db
     */
    protected static $db;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes)->setConnection();
    }

    public static function query(): QueryBuilder
    {
        return DB::getInstance()->getQuery()->setModel(new static());
    }

    /**
     * @param int $id
     * @return Model|null
     * @throws \Exception
     */
    public static function find(int $id)
    {
        $instance = new static();

        return static::$db->getQuery()
            ->setModel($instance)
            ->where([$instance->primaryKey => $id])
            ->first();
    }

    /**
     * @return Collection
     */
    public static function all(): Collection
    {
        $instance = new static();

        return DB::getInstance()
            ->getQuery()
            ->setModel($instance)
            ->get();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function count(): int
    {
        return DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel(new static())
            ->count();
    }

    /**
     * @param array $attributes
     * @return Collection|bool|int
     * @throws \Exception
     */
    protected function create(array $attributes)
    {
        return DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel($this)
            ->create($attributes)
            ->execute();
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws \Exception
     */
    public function update(array $attributes): bool
    {
        if (count($attributes) > 0) {
            return DB::getInstance()
                ->getQuery()
                ->setModel($this)
                ->update($this->fromFillable($attributes))
                ->save();
        } elseif (count($this->data)) {
            $attributes = [];

            foreach ($this->fromFillable($this->data) as $key => $value) {
                $attributes[$key] = $value;
            }

            $this->reset();

            return $this->update($attributes);
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        $attributes = [];

        foreach ($this->fromFillable($this->data) as $key => $value) {
            $attributes[$key] = $value;
        }

        $this->reset();

        return $this->update($attributes);
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        return DB::getInstance()
            ->setModel($this)
            ->delete()
            ->where([$this->primaryKey => $this->{$this->primaryKey}])
            ->execute();
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function fill(array $attributes)
    {
        if (count($attributes) === 0) return $this;

        foreach ($this->fromFillable($attributes) as $key => $value) {
            if (in_array($key, $this->dates)) {
                $this->data[$key] = (new \DateTime($value))->format('Y-m-d H:i:s');
            } else {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function fromFillable(array $attributes): array
    {
        return collect($attributes)->filter(function ($value, string $key) {
            return in_array($key, $this->fillable) || $key === $this->getPrimary();
        })->all();
    }

    public function setConnection()
    {
        static::$db = DB::getInstance();

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @param null $key
     * @return $this
     */
    public function reset($key = null)
    {
        if (!is_null($key) && isset($this->data[$key])) {
            unset($this->data[$key]);
        } elseif (is_null($key)) {
            $this->data = [];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimary(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->fillable) || $key === $this->getPrimary()) {
            $this->data[$key] = $value;
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } elseif (method_exists($this, $key)) {
            return $this->$key()->getResults();
        }

        return null;
    }

    public static function __callStatic(string $method, array $arguments)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = DB::getInstance()->getQuery();
        $instance = new static();

        if (method_exists($query, $method) && !method_exists($instance, $method)) {
            return $query->setModel($instance)->$method(...$arguments);
        } elseif (method_exists($instance, $method)) {
            return $instance->$method(...$arguments);
        } else {
            throw new \BadMethodCallException(sprintf('Метод %s не объявлен в классе %s', $method, __CLASS__));
        }
    }

    public function __call(string $method, array $arguments)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel($this);

        if (method_exists($query, $method) && !method_exists($this, $method)) {

            if ($method === 'where') {
                return $query->where([$this->primaryKey => $this->{$this->primaryKey}]);
            } else {
                return $query->$method(...$arguments);
            }

        } elseif (method_exists($this, sprintf('scope%s', ucfirst($method)))) {
            return $this->{sprintf('scope%s', ucfirst($method))}($query, ...$arguments);
        } else {
            throw new \BadMethodCallException(sprintf('Метод %s не объявлен в классе %s', $method, __CLASS__));
        }
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return sprintf(
            '%s_%s',
            str_replace('App/Models', '', $this->camelCaseToUnderScore(class_basename($this))),
            $this->getPrimary()
        );
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->fromFillable($this->data);
    }
}