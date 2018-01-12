<?php

namespace Fomvasss\Repository\Contracts;

interface RepositoryInterface
{
    public function model();

    public function boot();

    public function all(array $columns = ['*']);

    public function count(): int;

    public function find($id, array $columns = ['*'], string $column = 'id');

    public function findOrFail($id, array $columns = ['*'], string $column = 'id');

    public function first(array $columns = ['*']);

    public function create(array $data);

    public function createMultiple(array $data);

    public function update($id, array $data, string $column = 'id');

    public function updateOrFail($id, array $data, string $column = 'id');

    public function updateOrCreate(array $attributes, array $values = []);

    public function delete($id, string $column = 'id');

    public function deleteOrFail($id, string $column = 'id');

    public function paginate($limit = 15, array $columns = ['*'], $pageName = 'page', $page = null);

    public function simplePaginate($perPage = 15, array $columns = ['*']);

    public function pluck($column, $key = null);

    public function limit(int $limit);

    public function extraQueryParams($extraQueryParams = null);

    public function maxPerPage(int $maxPerPage);

    public function scope(string $method, ...$args);

    public function where($column, $value, $operator = '=');

    public function whereIn($column, $value);

    public function orderBy($column, $value = 'asc');

    public function with($relations);

    public function get(array $columns = ['*']);
}
