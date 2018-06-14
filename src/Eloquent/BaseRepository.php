<?php

namespace Fomvasss\Repository\Eloquent;

use Fomvasss\Repository\Contracts\RepositoryInterface;
use Fomvasss\Repository\Events\RepositoryEntityCreated;
use Fomvasss\Repository\Events\RepositoryEntityDeleted;
use Fomvasss\Repository\Events\RepositoryEntityUpdated;
use Fomvasss\Repository\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Collection;
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
     * String or array - extra params for the repository: filters, order,...
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

    public function __construct()
    {
        $this->makeModel();
        $this->boot();
    }

    public function boot()
    {
        //
    }

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

    protected function applyExtras()
    {
        return $this->newQuery()->eagerLoad()->setClauses()->setScopes();
    }

    protected function unsetClauses()
    {
        $this->wheres = [];
        $this->whereIns = [];
        $this->scopes = [];
        $this->take = null;
        $this->extraQueryParams = null;

        return $this;
    }

    public function all(array $columns = ['*'])
    {
        $this->newQuery()->eagerLoad();

        $models = $this->query->get($columns);

        $this->unsetClauses();

        return $models;
    }

    public function paginate($perPage = null, array $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyExtras();

        $this->preparePerPage($perPage);

        $models = $this->query->paginate($this->perPage, $columns, $pageName, $page);

        $this->unsetClauses();

        return $models;
    }

    public function simplePaginate($perPage = null, array $columns = ['*'])
    {
        $this->applyExtras();

        $this->preparePerPage($perPage);

        $models = $this->query->simplePaginate($this->perPage, $columns);

        $this->unsetClauses();

        return $models;
    }

    public function pluck($column, $key = null)
    {
        $this->applyExtras();

        $models = $this->query->pluck($column, $key);

        $this->unsetClauses();

        return $models;
    }

    public function count() : int
    {
        $this->applyExtras();

        $count = $this->get()->count();

        $this->unsetClauses();

        return $count;
    }

    public function find($id, array $columns = ['*'], string $column = 'id')
    {
        $this->applyExtras();

        $model = $this->query->where($column, $id)->first($columns);

        $this->unsetClauses();

        return $model;
    }

    public function findOrFail($id, array $columns = ['*'], string $column = 'id')
    {
        $this->applyExtras();

        $model = $this->query->where($column, $id)->firstOrFail($columns);

        $this->unsetClauses();

        return $model;
    }

    public function first(array $columns = ['*'])
    {
        $this->applyExtras();

        $model = $this->query->first($columns);

        $this->unsetClauses();

        return $model;
    }

    public function firstOrFail(array $columns = ['*'])
    {
        $this->applyExtras();

        $model = $this->query->firstOrFail($columns);

        $this->unsetClauses();

        return $model;
    }

    public function create(array $data)
    {
        $this->unsetClauses();

        $model = $this->model->create($data);

        event(new RepositoryEntityCreated($this, $model));

        return $model;
    }

    public function createMultiple(array $data)
    {
        $models = new Collection();

        foreach ($data as $d) {
            $models->push($this->create($d));
        }

        return $models;
    }

    public function update($id, array $data, string $column = 'id')
    {
        $this->unsetClauses();

        if (optional($model = $this->find($id, ['*'], $column))->update($data)) {
            event(new RepositoryEntityUpdated($this, $model));
        }

        return $model;
    }

    public function updateOrFail($id, array $data, string $column = 'id')
    {
        $this->unsetClauses();

        if (($model = $this->findOrFail($id, ['*'], $column))->update($data)) {
            event(new RepositoryEntityUpdated($this, $model));
        }

        return $model;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->unsetClauses();

        $model = $this->model->updateOrCreate($attributes, $values);
        event(new RepositoryEntityUpdated($this, $model));

        return $model;
    }

    public function delete($id, string $column = 'id') : bool
    {
        $this->unsetClauses();

        if ($result = optional($model = $this->find($id, ['*'], $column))->delete()) {
            event(new RepositoryEntityDeleted($this, $model));
        }

        return $result;
    }

    public function deleteOrFail($id, string $column = 'id') : bool
    {
        $this->unsetClauses();

        if ($result = ($model = $this->findOrFail($id, ['*'], $column))->delete()) {
            event(new RepositoryEntityDeleted($this, $model));
        }

        return $result;
    }

    public function deleteMultipleById(array $ids)
    {
        // not event fire!
        return $this->model->destroy($ids);
    }

    public function limit(int $limit)
    {
        $this->take = $limit;

        return $this;
    }

    public function extraQueryParams($extraQueryParams = null)
    {
        $this->extraQueryParams = $extraQueryParams;

        return $this;
    }

    public function maxPerPage(int $maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;

        return $this;
    }
    
    protected function preparePerPage($perPage = null)
    {
        $perPage = (int)$perPage ?: request('per_page', 15);
        $this->perPage = ($perPage > $this->maxPerPage || $perPage < $this->minPerPage) ? $this->perPage : $perPage;

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    public function where($column, $value, $operator = '=')
    {
        $this->wheres[] = compact('column', 'value', 'operator');

        return $this;
    }

    public function whereIn($column, $values)
    {
        $values = is_array($values) ? $values : [$values];

        $this->whereIns[] = compact('column', 'values');

        return $this;
    }

    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->with = $relations;

        return $this;
    }

    public function scope(string $method, ...$args)
    {
        $this->scopes[] = [$method, $args];

        return $this;
    }

    public function scopes(...$attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute) && ! empty($attribute) && is_string($attribute[0])) {
                $method = $attribute[0];
                unset($attribute[0]);
                $this->scopes[] = [$method, $attribute];
            } elseif (is_string($attribute)) {
                $this->scopes[] = [$attribute, []];
            }
        }

        return $this;
    }

    public function get(array $columns = ['*'])
    {
        $this->applyExtras();

        $models = $this->query->get($columns);

        $this->unsetClauses();

        return $models;
    }

    protected function eagerLoad()
    {
        foreach ($this->with as $relation) {
            $this->query->with($relation);
        }

        return $this;
    }

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

    protected function setScopes()
    {
        foreach ($this->scopes as $scope) {
            list($method, $args) = $scope;
            $this->query->$method(...$args);
        }

        return $this;
    }
}
