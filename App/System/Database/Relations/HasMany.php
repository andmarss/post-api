<?php


namespace App\System\Database\Relations;


use App\Models\Model;
use App\System\Collection;
use App\System\Database\DB;
use App\System\Database\QueryBuilder;

class HasMany
{
    protected $model;

    protected $relatedModel;

    protected $relatedModelClass;

    protected $relations = [];

    protected $foreignKey;

    protected $ownerKey;

    public function __construct($model, string $relatedModel, string $foreignKey = null, string $ownerKey = null)
    {
        $this->model = $model;
        $this->relatedModel($relatedModel);
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * @return Collection|null
     * @throws \Exception
     */
    public function getResults(): ?Collection
    {
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->model->getForeignKey();
        }

        if (is_null($this->ownerKey)) {
            $this->ownerKey = $this->model->getPrimary();
        }

        return DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel($this->relatedModel)
            ->where([$this->foreignKey => $this->model->{$this->ownerKey}])
            ->get();
    }

    /**
     * @param string $class
     * @return $this
     */
    protected function relatedModel(string $class)
    {
        $class = str_replace('/', '\\', $class);
        $this->relatedModelClass = $class;
        $this->relatedModel = new $class();

        return $this;
    }
}