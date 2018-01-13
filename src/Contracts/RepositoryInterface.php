<?php

namespace Fomvasss\Repository\Contracts;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function model();

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*']);

    /**
     * @param int $limit
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($limit = 15, array $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = 15, array $columns = ['*']);

    /**
     * @param $column
     * @param null $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($column, $key = null);

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param $id
     * @param array $columns
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, array $columns = ['*'], string $column = 'id');

    /**
     * @param $id
     * @param array $columns
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, array $columns = ['*'], string $column = 'id');

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first(array $columns = ['*']);

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultiple(array $data);

    /**
     * @param $id
     * @param array $data
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function update($id, array $data, string $column = 'id');

    /**
     * @param $id
     * @param array $data
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrFail($id, array $data, string $column = 'id');

    /**
     * @param array $attributes
     * @param array $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * @param $id
     * @param string $column
     * @return bool
     */
    public function delete($id, string $column = 'id');

    /**
     * @param array $ids
     * @return int
     */
    public function deleteMultipleById(array $ids);

    /**
     * @param $id
     * @param string $column
     * @return bool
     */
    public function deleteOrFail($id, string $column = 'id');

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit);

    /**
     * @param null $extraQueryParams
     * @return $this
     */
    public function extraQueryParams($extraQueryParams = null);

    /**
     * @param int $maxPerPage
     * @return $this
     */
    public function maxPerPage(int $maxPerPage);

    /**
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function where($column, $value, $operator = '=');

    /**
     * @param $column
     * @param $value
     * @return $this
     */
    public function whereIn($column, $value);

    /**
     * @param $column
     * @param string $value
     * @return $this
     */
    public function orderBy($column, $value = 'asc');

    /**
     * @param $relations
     * @return $this
     */
    public function with($relations);

    /**
     * @param string $method
     * @param array ...$args
     * @return $this
     */
    public function scope(string $method, ...$args);

    /**
     * @param array $columns
     * @return $this
     */
    public function get(array $columns = ['*']);
}
