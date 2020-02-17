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
}