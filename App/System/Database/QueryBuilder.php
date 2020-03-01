<?php

namespace App\System\Database;

use App\Models\Model;
use App\System\Collection;

class QueryBuilder
{
    protected $sql = [
        'select'    => '',
        'update'    => '',
        'insert'    => '',
        'table'     => '',
        'joins'     => [],
        'where'     => [],
        'limit'     => '',
        'order_by'  => '',
        'order_how' => 'ASC',
        'delete'    => ''
    ];

    protected $select = false;
    protected $update = false;
    protected $insert = false;
    protected $delete = false;
    protected $exists = false;
    /**
     * @var Model $model
     */
    protected $model;

    protected static $operators = ['=', '>', '<', '<=', '>=', '<>'];
    /**
     * @var null|Collection|Model $result
     */
    protected $result;

    /**
     * @param string $fields
     * @return QueryBuilder
     */
    public function select(string $fields = '*'): QueryBuilder
    {
        if(is_string($fields)) {
            $this->sql['select'] = "SELECT {$fields}";
        } elseif (is_array($fields) && count($fields) > 0) {
            $fields = implode(', ', $fields);

            $this->sql['select'] = "SELECT ${fields}";
        } elseif (func_num_args() > 1) {
            $fields = implode(', ', func_get_args());

            $this->sql['select'] = "SELECT ${fields}";
        }

        $this->resetActions();

        $this->select = true;

        return $this;
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table): QueryBuilder
    {
        $this->sql['table'] = $table;

        return $this;
    }

    /**
     * Проверка существования таблицы
     * @return bool
     * @throws \Exception
     */
    public function exists(): bool
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        $this->select('*');

        $table = $this->sql['table'];

        $this->table('information_schema.tables');

        $this->where(['table_schema' => config('connections/database/name'), 'table_name' => $table]);

        $this->exists = true;

        return count(DB::getInstance()->setQuery($this)->query(null)) > 0;
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws \Exception
     */
    public function rowExists(array $attributes): bool
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        $this->select('*');

        $this->where($attributes);

        return DB::getInstance()->setQuery($this)->query()->count() > 0;
    }

    /**
     * @param array $conditions
     * @return QueryBuilder
     * @throws \Exception
     */
    public function where($conditions = []): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if (func_num_args() === 3) { // where(field,operator,value)

            /**
             * @var string $field
             * @var string $operator
             * @var string $value
             */
            [$field, $operator, $value] = func_get_args();

            if(in_array($operator, static::$operators)) {
                $this->sql['where'][] = sprintf('%s%s%s', $field, $operator, $this->escape($value));
            }

        } elseif (is_array($conditions) && count($conditions) > 0) {

            $where = implode(' AND ', array_map(function ($key, $value){
                return sprintf('%s=%s', $key, $this->escape($value));
            }, array_keys($conditions), array_values($conditions)));

            $this->sql['where'][] = $where;

        }

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param null $selectedField
     * @return $this
     * @throws \Exception
     */
    public function whereIn(string $field, $value, $selectedField = null): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(is_callable($value)) { // $value - функция, параметром которой является экземпляр конструктора запроса

            $this->sql['where'][] = sprintf('%s IN (%s)', $field, call_user_func($value, (new static())));

        } elseif (is_string($value) && is_string($selectedField)) {

            $this->sql['where'][] = sprintf("%s IN (SELECT %s FROM %s)", $field, $selectedField, $value);

        } elseif (is_array($value)) {

            $values = implode(', ', array_values($value));
            $this->sql['where'][] = sprintf("%s IN (%s)", $field, $values);

        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @param string|null $selectedField
     * @return QueryBuilder
     * @throws \Exception
     */
    public function whereNotIn(string $field, $value, string $selectedField = null): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(is_callable($value)) { // $value - функция, параметром которой является экземпляр конструктора запроса

            $this->sql['where'][] = sprintf('%s NOT IN (%s)', $field, call_user_func($value, new static()));

        } elseif (is_string($value) && is_string($selectedField)) {

            $this->sql['where'][] = sprintf("%s NOT IN (SELECT %s FROM %s)", $field, $selectedField, $value);

        } elseif (is_array($value)) {

            $values = implode(', ', array_values($value));
            $this->sql['where'][] = sprintf("%s NOT IN (%s)", $field, $values);

        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     */
    public function orWhere($conditions = []): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if (count($this->sql['where']) > 0) {

            if (func_num_args() === 3) { // where(field,operator,value)

                /**
                 * @var string $field
                 * @var string $operator
                 * @var string $value
                 */
                [$field, $operator, $value] = func_get_args();

                if(in_array($operator, static::$operators)) {
                    $this->sql['where'][] = sprintf('OR (%s%s%s)', $field, $operator, $value);
                }

            } elseif (is_array($conditions) && count($conditions) > 0) {

                $where = sprintf(' OR (%s)', implode(' AND ', array_map(function ($key, $value){
                    return sprintf('%s=%s', $key, $this->escape($value));
                }, array_keys($conditions), array_values($conditions))));

                $this->sql['where'][] = $where;

            }

        } else {
            throw new \Exception('Сперва должен быть выбран метод "where"');
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     */
    public function whereLike($conditions = []): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if (is_array($conditions) && count($conditions) !== 3 && count($conditions) > 0) {

            $where = implode(' AND ', array_map(function ($key, $value){
                return sprintf('%s LIKE "%%%s%%"', $key, $value);
            }, array_keys($conditions), array_values($conditions)));

            $this->sql['where'][] = $where;
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     */
    public function orWhereLike($conditions = []): QueryBuilder
    {
        if(!$this->sql['select'] && !$this->sql['delete'] && !$this->sql['update']) {
            $this->select();
        }

        if(!isset($this->sql['table']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if (count($this->sql['where']) > 0) {

            if (is_array($conditions) && count($conditions) !== 3 && count($conditions) > 0) {

                $where = sprintf(' OR (%s)', implode(' AND ', array_map(function ($key, $value){
                    return sprintf('%s LIKE "%%%s%%"', $key, $value);
                }, array_keys($conditions), array_values($conditions))));

                $this->sql['where'][] = $where;
            }

        } else {
            throw new \Exception('Сперва должен быть выбран метод "where"');
        }

        return $this;
    }

    /**
     * @param string $field
     * @return QueryBuilder
     */
    public function orderBy(string $field = null): QueryBuilder
    {
        if (is_null($field)) {
            $field = 'id';
        }

        $this->sql['order_by'] = $field;

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function asc(): QueryBuilder
    {
        $this->sql['order_how'] = 'ASC';

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function desc(): QueryBuilder
    {
        $this->sql['order_how'] = 'DESC';

        return $this;
    }

    /**
     * @param int $from
     * @param int|null $length
     * @return QueryBuilder
     */
    public function limit(int $from, int $length = null): QueryBuilder
    {
        if (!is_null($length)) {
            $this->sql['limit'] = sprintf('LIMIT %s, %s', (string) $from, (string) $length);
        } elseif (is_null($length)) {
            $this->sql['limit'] = sprintf('LIMIT %s', (string) $from);
        }

        return $this;
    }

    /**
     * @param string $table
     * @param $first
     * @param string|null $operator
     * @param string|null $second
     * @param string $type
     * @return QueryBuilder
     */
    public function join(string $table, $first, $operator = null, $second = null, $type = 'INNER'): QueryBuilder
    {
        /**
         * @var Join $join
         */
        $join = new Join($table, $type);

        if ($first instanceof \Closure) {
            $join = call_user_func($first, $join);

            $this->sql['joins'][] = $join->compile();
        } else {
            if (!in_array((string) $operator, static::$operators)) {
                throw new \InvalidArgumentException(sprintf('Неизвестный оператор "%s"', (string) $operator));
            }

            $this->sql['joins'][] = $join->on($first, $operator, $second)->compile();
        }

        return $this;
    }

    /**
     * @param string $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return QueryBuilder
     */
    public function leftJoin(string $table, $first, $operator = null, $second = null): QueryBuilder
    {
        return $this->join($table, $first, $operator, $second, Join::LEFT_JOIN);
    }

    /**
     * @param string $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return QueryBuilder
     */
    public function rightJoin(string $table, $first, $operator = null, $second = null): QueryBuilder
    {
        return $this->join($table, $first, $operator, $second, Join::RIGHT_JOIN);
    }

    /**
     * @param string $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return QueryBuilder
     */
    public function outerJoin(string $table, $first, $operator = null, $second = null): QueryBuilder
    {
        return $this->join($table, $first, $operator, $second, Join::OUTER_JOIN);
    }

    /**
     * @param array $attributes
     * @return QueryBuilder
     * @throws \Exception
     */
    public function update(array $attributes): QueryBuilder
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        if (count($attributes) === 0) {
            throw new \Exception('При обновлении базы передан пустой массив аттрибутов');
        }

        if (!is_null($this->model)) {
            $attributes = $this->model->fromFillable($attributes);
        }

        $update = sprintf('UPDATE %s SET %s', $this->sql['table'], implode(', ', array_map(function ($key, $value) {
            return sprintf('%s=%s', $key, $this->escape($value));
        }, array_keys($attributes), array_values($attributes))));

        $this->sql['update'] = $update;

        $this->resetActions();

        $this->update = true;

        return $this;
    }

    /**
     * @param array $attributes
     * @return QueryBuilder
     * @throws \Exception
     */
    public function create(array $attributes): QueryBuilder
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        if (count($attributes) === 0) {
            throw new \Exception('При обновлении базы передан пустой массив аттрибутов');
        }

        if (!is_null($this->model)) {
            $attributes = $this->model->fromFillable($attributes);
        }

        $keys = implode(', ', array_keys($attributes));

        $values = implode(', ', array_map(function ($value) {
            return $this->escape($value);
        }, array_values($attributes)));

        $create = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->sql['table'], $keys, $values);

        $this->sql['insert'] = $create;

        $this->resetActions();

        $this->insert = true;

        return $this;
    }

    /**
     * @return QueryBuilder
     * @throws \Exception
     */
    public function delete(): QueryBuilder
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        $delete = sprintf('DELETE FROM %s', $this->sql['table']);

        $this->sql['delete'] = $delete;

        $this->resetActions();

        $this->delete = true;

        return $this;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        if (!$this->sql['table']) {
            throw new \Exception('Сперва нужно объявить таблицу');
        }

        $this->reset(['delete', 'update', 'insert']);

        $this->resetActions();

        $this->select = true;

        $this->select('COUNT(*)');

        $sql = $this->compile();

        return (int) DB::getInstance()->setQuery($this)->query($sql, true);
    }

    /**
     * @param Model $model
     * @return QueryBuilder
     */
    public function setModel(Model $model): QueryBuilder
    {
        $this->model = $model;

        return $this->table($this->model->getTable());
    }

    /**
     * @return mixed
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->sql[$key]);
    }

    /**
     * @param array|null $keys
     * @return QueryBuilder
     */
    protected function reset(array $keys = null): QueryBuilder
    {
        if (!is_null($keys)) {
            foreach ($keys as $key) {
                unset($this->sql[$key]);
            }
        } else {
            $this->sql = [
                'select' => [],
                'update' => [],
                'insert' => [],
                'table'  => '',
                'joins'  => [],
                'where'  => [],
                'limit'  => '',
                'order'  => '',
                'delete' => ''
            ];

            $this->resetActions();
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function resetActions(): QueryBuilder
    {
        $this->select = false;
        $this->insert = false;
        $this->delete = false;
        $this->update = false;

        return $this;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function escape(string $value): string
    {
        return DB::escape($value);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function compile(): string
    {
        /**
         * @var string $sql
         */
        $sql = '';

        if ($this->select) {
            $select = $this->sql['select'];

            $table = $this->sql['table'];

            if (count($this->sql['joins']) > 0) {
                $joins = implode("\n", array_values($this->sql['joins']));
            } else {
                $joins = '';
            }

            if (count($this->sql['where']) > 0) {
                $where = sprintf('WHERE %s', implode(" ", array_values($this->sql['where'])));
            } else {
                $where = '';
            }

            if (isset($this->sql['order_by']) && $this->sql['order_by'] !== '') {
                $order_by = $this->sql['order_by'];
            }

            if (isset($this->sql['order_how'])) {
                $order_how = $this->sql['order_how'];
            } elseif (isset($this->sql['order_by']) && !isset($this->sql['order_how'])) {
                $order_how = 'ASC';
            }

            if (isset($order_by) && isset($order_how)) {
                $order = sprintf('ORDER BY %s %s', $order_by, $order_how);
            } else {
                $order = '';
            }

            if ($this->sql['limit']) {
                $limit = $this->sql['limit'];
            } else {
                $limit = '';
            }

            $sql = sprintf(
                "%s FROM %s %s %s %s %s", $select, $table, $joins, $where, $order, $limit
            );
        } elseif ($this->insert) {
            /**
             * @var string $insert
             */
            $insert = $this->sql['insert'];

            $sql = $insert;
        } elseif ($this->update) {
            if (count($this->sql['where']) === 0) {
                throw new \Exception('Для обновления таблицы должно быть объявлено минимум одно условие where');
            }
            /**
             * @var string $update
             */
            $update = $this->sql['update'];

            if (count($this->sql['joins']) > 0) {
                $joins = implode("\n", array_values($this->sql['joins']));
            } else {
                $joins = '';
            }

            if (count($this->sql['where']) > 0) {
                $where = sprintf('WHERE %s', implode(" ", array_values($this->sql['where'])));
            } else {
                $where = '';
            }

            $sql = sprintf('%s %s %s', $update, $joins, $where);
        } elseif ($this->delete) {
            if (count($this->sql['where']) === 0) {
                throw new \Exception('Для удаления из таблицы должно быть объявлено минимум одно условие where');
            }

            $delete = $this->sql['delete'];

            if (count($this->sql['joins']) > 0) {
                $joins = implode("\n", array_values($this->sql['joins']));
            } else {
                $joins = '';
            }

            if (count($this->sql['where']) > 0) {
                $where = sprintf('WHERE %s', implode(" ", array_values($this->sql['where'])));
            } else {
                $where = '';
            }

            $sql = sprintf('%s %s %s', $delete, $joins, $where);
        }

        return preg_replace('/\s+/', ' ', trim($sql));
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        if ($this->select) {
            /**
             * @var Collection $result
             */
            $result = DB::getInstance()->setQuery($this)->query();

            $this->reset();

            return $result;
        } elseif ($this->allActionsIsFalse()) {
            $this->select();

            return $this->get();
        }

        return collect([]);
    }

    public function first()
    {
        if ($this->select) {
            /**
             * @var Collection $result
             */
            $result = DB::getInstance()->setQuery($this)->query();

            $this->reset();

            return $result->first();
        } elseif ($this->allActionsIsFalse()) {
            $this->select();

            return $this->first();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if ($this->update) {
            DB::getInstance()->setQuery($this)->query();

            $this->reset();

            return true;
        }

        return false;
    }

    public function execute()
    {
        if ($this->select) {
            return $this->get();
        } elseif ($this->update) {
            return $this->save();
        } elseif ($this->insert) {
            DB::getInstance()->setQuery($this)->query();

            $model = $this->getModel();

            $this->reset();

            return !is_null($model) ? get_class($model)::find(DB::lastId()) : DB::lastId();
        } elseif ($this->delete) {
            DB::getInstance()->setQuery($this)->query();

            $this->reset();

            return true;
        }
    }

    /**
     * @return bool
     */
    public function isSelect(): bool
    {
        return $this->select;
    }
    /**
     * @return bool
     */
    public function isInsert(): bool
    {
        return $this->insert;
    }
    /**
     * @return bool
     */
    public function isUpdate(): bool
    {
        return $this->update;
    }
    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->delete;
    }

    /**
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->exists;
    }

    /**
     * @return bool
     */
    public function allActionsIsFalse(): bool
    {
        return !$this->select && !$this->delete && !$this->delete && !$this->update;
    }

    public function __toString(): string
    {
        return $this->compile();
    }

}