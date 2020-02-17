<?php


namespace App\System\Database\Relations;


use App\Models\Model;
use App\System\Database\DB;
use App\System\Database\QueryBuilder;

class BelongsTo
{
    /**
     * @var Model $model
     */
    protected $model;
    /**
     * @var Model|null $relatedModel
     */
    protected $relatedModel;
    /**
     * @var string $relatedModelClass
     */
    protected $relatedModelClass;
    /**
     * @var array $relations
     */
    protected $relations = [];
    /**
     * @var string $foreignKey
     */
    protected $foreignKey;
    /**
     * @var string $ownerKey
     */
    protected $ownerKey;

    public function __construct($model, string $relatedModel, string $foreignKey = null, string $ownerKey = null)
    {
        $this->model = $model;
        $this->relatedModel($relatedModel);
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * @return Model|null
     * @throws \Exception
     */
    public function getResults(): ?Model
    {
        if (is_null($this->foreignKey)) {
            $this->foreignKey = $this->relatedModel->getPrimary();
        }

        if (is_null($this->ownerKey)) {
            $this->ownerKey = $this->relatedModel->getForeignKey();
        }

        return DB::getInstance()
            ->setQuery(new QueryBuilder())
            ->getQuery()
            ->setModel($this->relatedModel)
            ->where([$this->foreignKey => $this->model->{$this->ownerKey}])
            ->first();
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