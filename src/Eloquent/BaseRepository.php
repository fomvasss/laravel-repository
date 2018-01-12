<?php

namespace Fomvasss\Repository\Eloquent;

use Fomvasss\Repository\Contracts\RepositoryInterface;
use Fomvasss\Repository\Events\RepositoryEntityCreated;
use Fomvasss\Repository\Events\RepositoryEntityDeleted;
use Fomvasss\Repository\Events\RepositoryEntityUpdated;
use Fomvasss\Repository\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository
 *
 * @package \App\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The repository model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The query builder.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Alias for the query limit.
     *
     * @var int
     */
    protected $take;

    /**
     * Array of related models to eager load.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Array of one or more where clause parameters.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * Array of one or more where in clause parameters.
     *
     * @var array
     */
    protected $whereIns = [];

    /**
     * Array of one or more ORDER BY column/value pairs.
     *
     * @var array
     */
    protected $orderBys = [];

    /**
     * Array of scope methods to call on the model.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * String or array - extra params for repository: filters, order,...
     *
     * @var string|array
     */
    protected $extraQueryParams;

    /**
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var int
     */
    protected $minPerPage = 1;

    /**
     * @var int
     */
    protected $maxPerPage = 150;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->makeModel();
        $this->boot();
    }

    public function boot()
    {
        //
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws \Fomvasss\Repository\Exceptions\RepositoryException
     */
    public function makeModel()
    {
        $model = app()->make($this->model());

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$model} must be an instance of " . Model::class);
        }

        return $this->model = $model;
    }

    /**
     * Create a new instance of the model's query builder.
     *
     * @return $this
     */
    protected function newQuery()
    {
        $this->query = $this->model->newQuery();

        return $this;
    }

    /**
     * Get all the model records in the database.
     *
     * @param array $columns
     *
     * @return Collection|static[]
     */
    public function all(array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad();

        $models = $this->query->get($columns);

        $this->unsetClauses();

        return $models;
    }

    /**
     * Get all the specified model records in the database.
     *
     * @param array $columns
     *
     * @return Collection|static[]
     */
    public function get(array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $models = $this->query->get($columns);

        $this->unsetClauses();

        return $models;
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, array $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $this->checkPerPage($perPage);

        $models = $this->query->paginate($this->perPage, $columns, $pageName, $page);

        $this->unsetClauses();

        return $models;
    }

    /**
     * @param int $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($perPage = 15, array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $this->checkPerPage($perPage);

        $models = $this->query->simplePaginate($this->perPage, $columns);

        $this->unsetClauses();

        return $models;
    }

    public function pluck($column, $key = null)
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $models = $this->query->pluck($column, $key);

        $this->unsetClauses();

        return $models;
    }

    /**
     * Count the number of specified model records in the database.
     *
     * @return int
     */
    public function count() : int
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        return $this->get()->count();
    }

    /**
     * Create a new model record in the database.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        $this->unsetClauses();

        $model = $this->model->create($data);

        event(new RepositoryEntityCreated($this, $model));

        return $model;
    }

    /**
     * Create one or more new model records in the database.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultiple(array $data)
    {
        $models = new Collection();

        foreach ($data as $d) {
            $models->push($this->create($d));
        }

        return $models;
    }

    /**
     * Get the first specified model record from the database.
     *
     * @param $id
     * @param array $columns
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function find($id, array $columns = ['*'], string $column = 'id')
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $model = $this->query->where($column, $id)->first($columns);

        $this->unsetClauses();

        return $model;
    }

    /**
     * Get the first specified model record from the database.
     *
     * @param $id
     * @param array $columns
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function findOrFail($id, array $columns = ['*'], string $column = 'id')
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $model = $this->query->where($column, $id)->firstOrFail($columns);

        $this->unsetClauses();

        return $model;
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function first(array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad()->setClauses()->setScopes();

        $model = $this->query->first($columns);

        $this->unsetClauses();

        return $model;
    }

    /**
     * Update the specified model record in the database.
     *
     * @param $id
     * @param array $data
     * @param string $column
     *
     * @return mixed
     */
    public function update($id, array $data, string $column = 'id')
    {
        $this->unsetClauses();

        if (optional($model = $this->find($id, ['*'], $column))->update($data)) {
            event(new RepositoryEntityUpdated($this, $model));
        }

        return $this->find($id, ['*'], $column);
    }

    /**
     * Update the specified model record in the database.
     *
     * @param $id
     * @param array $data
     * @param string $column
     *
     * @return mixed
     */
    public function updateOrFail($id, array $data, string $column = 'id')
    {
        $this->unsetClauses();
        if (($model = $this->findOrFail($id, ['*'], $column))->update($data)) {
            event(new RepositoryEntityUpdated($this, $model));
        }

        return $this->find($id, ['*'], $column);
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->unsetClauses();

        $model = $this->model->updateOrCreate($attributes, $values);
        event(new RepositoryEntityUpdated($this, $model));

        return $model;
    }

    /**
     * Delete one or more model records from the database.
     *
     * @param $id
     * @param string $column
     *
     * @return bool
     */
    public function delete($id, string $column = 'id') : bool
    {
        $this->unsetClauses();

        if ($result = optional($model = $this->find($id, ['*'], $column))->delete()) {
            event(new RepositoryEntityDeleted($this, $model));
        }

        return $result;
    }

    /**
     * Delete one or more model records from the database.
     *
     * @param $id
     * @param string $column
     *
     * @return bool
     */
    public function deleteOrFail($id, string $column = 'id') : bool
    {
        $this->unsetClauses();

        if ($result = ($model = $this->findOrFail($id, ['*'], $column))->delete()) {
            event(new RepositoryEntityDeleted($this, $model));
        }

        return $result;
    }

    /**
     * Delete multiple records.
     *
     * @param array $ids
     *
     * @return int
     */
    public function deleteMultipleById(array $ids) : int
    {
        // not event fire!
        return $this->model->destroy($ids);
    }

    /**
     * Set the query limit.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->take = $limit;

        return $this;
    }

    /**
     * @param null $extraQueryParams
     * @return $this
     */
    public function extraQueryParams($extraQueryParams = null)
    {
        $this->extraQueryParams = $extraQueryParams;

        return $this;
    }

    /**
     * @param int $maxPerPage
     * @return $this
     */
    public function maxPerPage(int $maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;

        return $this;
    }


    /**
     * @param int $perPage
     * @return $this
     */
    protected function checkPerPage($perPage = 15)
    {
        $perPage = (int) $perPage;
        $this->perPage = ($perPage > $this->maxPerPage || $perPage < $this->minPerPage) ? $this->perPage : $perPage;

        return $this;
    }

    /**
     * Set an ORDER BY clause.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Add a simple where clause to the query.
     *
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function where($column, $value, $operator = '=')
    {
        $this->wheres[] = compact('column', 'value', 'operator');

        return $this;
    }

    /**
     * Add a simple where in clause to the query.
     *
     * @param string $column
     * @param mixed  $values
     *
     * @return $this
     */
    public function whereIn($column, $values)
    {
        $values = is_array($values) ? $values : [$values];

        $this->whereIns[] = compact('column', 'values');

        return $this;
    }

    /**
     * Set Eloquent relationships to eager load.
     *
     * @param $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->with = $relations;

        return $this;
    }

    /**
     * Add relationships to the query builder to eager load.
     *
     * @return $this
     */
    protected function eagerLoad()
    {
        foreach ($this->with as $relation) {
            $this->query->with($relation);
        }

        return $this;
    }

    /**
     * Set clauses on the query builder.
     *
     * @return $this
     */
    protected function setClauses()
    {
        foreach ($this->wheres as $where) {
            $this->query->where($where['column'], $where['operator'], $where['value']);
        }

        foreach ($this->whereIns as $whereIn) {
            $this->query->whereIn($whereIn['column'], $whereIn['values']);
        }

        foreach ($this->orderBys as $orders) {
            $this->query->orderBy($orders['column'], $orders['direction']);
        }

        if (isset($this->take) and ! is_null($this->take)) {
            $this->query->take($this->take);
        }

        return $this;
    }

    /**
     * @param string $method
     * @param array ...$args
     * @return $this
     */
    public function scope(string $method, ...$args)
    {
        $this->scopes = array_merge($this->scopes, [$method => $args]);

        return $this;
    }

    /**
     * Set query scopes.
     *
     * @return $this
     */
    protected function setScopes()
    {
        foreach ($this->scopes as $method => $args) {
            //$this->query->$method(implode(', ', $args));
            $this->query->$method($args);
        }

        return $this;
    }

    /**
     * Reset the query clause parameter arrays.
     *
     * @return $this
     */
    protected function unsetClauses()
    {
        $this->wheres = [];
        $this->whereIns = [];
        $this->scopes = [];
        $this->take = null;

        return $this;
    }
}
