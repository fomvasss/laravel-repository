<?php

namespace Fomvasss\Repository\Contracts;

use Illuminate\Cache\Repository as CacheRepository;

interface CacheableInterface
{
    /**
     * @param \Illuminate\Cache\Repository $repository
     * @return $this
     */
    public function setCacheRepo(CacheRepository $repository);

    /**
     * @return $this
     */
    public function setCacheOff();

    /**
     * @return bool
     */
    public function getCacheOff();

    /**
     * Return instance of Cache Repository
     *
     * @return CacheRepository
     */
    public function getCacheRepo();

    /**
     * @return float|int
     */
    public function getCacheTime();

    /**
     * @param $method
     * @return mixed
     * @return float|int
     */
    public function getCacheTimeForMethod($method);

    /**
     * @return array
     */
    public function getCacheOnly(): array;

    /**
     * @return array
     */
    public function getCacheExcept(): array;

    public function all(array $columns = ['*']);

    public function get(array $columns = ['*']);

    public function paginate($perPage = 15, array $columns = ['*'], $pageName = 'page', $page = null);

    public function simplePaginate($perPage = 15, array $columns = ['*']);

    public function count(): int;

    public function find($id, array $columns = ['*'], string $column = 'id');

    public function findOrFail($id, array $columns = ['*'], string $column = 'id');

    public function first(array $columns = ['*']);

    public function firstOrFail(array $columns = ['*']);
}
