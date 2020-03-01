<?php

namespace App\System\Database\Relations;

use App\Models\Model;
use App\System\Database\DB;
use App\System\Database\QueryBuilder;

class BelongsToMany
{
    /**
     * @var string $table
     */
    protected $table;
    /**
     * @var string $foreignPivotKey
     */
    protected $foreignPivotKey;
    /**
     * @var string $relatedPivotKey
     */
    protected $relatedPivotKey;
    /**
     * @var Model $model
     */
    protected $model;
    /**
     * @var Model|null $relatedModel
     */
    protected $relatedModel;

    public function __construct($model, string $class, string $table = null, string $foreignPivotKey = null, string $relatedPivotKey = null)
    {
        $this->model = $model;
        $this->relatedModel($class);
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
    }

    /**
     * @return \App\System\Collection
     * @throws \Exception
     */
    public function getResults()
    {
        /**
         * @var array $tables
         */
        $tables = [$this->model->getTable(), $this->relatedModel->getTable()];

        sort($tables);

        if (is_null($this->table)) {
            $this->table = implode('_', $tables);
        }

        if (is_null($this->foreignPivotKey)) {
            $this->foreignPivotKey = $this->model->getForeignKey();
        }

        if (is_null($this->relatedPivotKey)) {
            $this->relatedPivotKey = $this->relatedModel->getForeignKey();
        }

        return DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel($this->relatedModel)
            ->whereIn($this->relatedModel->getPrimary(), function (QueryBuilder $builder) {
                return $builder
                    ->table($this->table)
                    ->select($this->relatedPivotKey)
                    ->where(
                        [$this->foreignPivotKey => $this->model->{$this->model->getPrimary()}]
                    );
            })->get();
    }

    /**
     * @param string $class
     * @return $this
     */
    protected function relatedModel(string $class)
    {
        $class = str_replace('/', '\\', $class);
        $this->relatedModel = new $class();

        return $this;
    }

    /**
     * @param null $ids
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function attach($ids = null, array $attributes = [])
    {
        if (!is_null($ids)) {

            if (!is_array($ids)) {
                $ids = (array) $ids;
            }

            $db = DB::getInstance()
                ->setQuery(new QueryBuilder())
                ->getQuery()
                ->table($this->table);

            foreach ($ids as $id) {
                $data = [$this->foreignPivotKey => $this->model->{$this->model->getPrimary()}, $this->relatedPivotKey => $id] + $attributes;
                // если такая связь уже есть - пропускаем
                if ($db->rowExists($data)) continue;

                $db->create($data)->execute();
            }
        }

        return $this;
    }

    /**
     * @param null $ids
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function detach($ids = null, array $attributes = [])
    {
        if (!is_null($ids)) {

            if (!is_array($ids)) {
                $ids = (array) $ids;
            }

            $db = DB::getInstance()
                ->setQuery(new QueryBuilder())
                ->getQuery()
                ->table($this->table);

            foreach ($ids as $id) {
                $data = [$this->foreignPivotKey => $this->model->{$this->model->getPrimary()}, $this->relatedPivotKey => $id] + $attributes;
                // если такой связи нет - пропускаем
                if (!$db->rowExists($data)) continue;

                $db->where($data)->delete()->execute();
            }
        } elseif (is_null($ids)) { // значит, нужно удалить все связи текущей модели со связующей таблицей
            $db = DB::getInstance()
                ->setQuery(new QueryBuilder())
                ->getQuery()
                ->table($this->table);

            $data = [$this->foreignPivotKey => $this->model->{$this->model->getPrimary()}];

            if (!$db->rowExists($data)) return $this;

            $db->where($data)->delete()->execute();
        }

        return $this;
    }
}