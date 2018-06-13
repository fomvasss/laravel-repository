<?php

namespace Fomvasss\Repository\Traits;

use Fomvasss\Repository\Contracts\CacheableInterface;
use Illuminate\Cache\Repository as CacheRepository;

/**
 * Trait CacheableRepository
 *
 * @property int $cacheTime
 * @property array $cacheTimeForMethod
 * @property string[] $cacheOnly
 * @property string[] $cacheExcept
 *
 * @package Fomvasss\Repository\Traits
 */
trait CacheableRepository
{
    protected $cacheRepo;

    protected $cacheOff;

    public function setCacheRepo(CacheRepository $repository)
    {
        $this->cacheRepo = $repository;

        return $this;
    }

    public function getCacheRepo()
    {
        return $this->cacheRepo ?? $this->cacheRepo = app(config('repository.cache.repository', 'cache'));
    }

    public function setCacheOff()
    {
        $this->cacheOff = true;

        return $this;
    }

    public function getCacheOff()
    {
        return $this->cacheOff ?? config('repository.cache.off', false);
    }

    public function getCacheTime()
    {
        return $this->cacheTime ?? config('repository.cache.time', 60);
    }

    public function getCacheTimeForMethod($method)
    {
        return ($this->cacheTimeForMethod ?? [])[$method] ?? $this->getCacheTime();
    }

    public function getCacheOnly(): array
    {
        return $this->cacheOnly ?? [];
    }

    public function getCacheExcept(): array
    {
        return $this->cacheExcept ?? [];
    }

    protected function cacheKeyName(string $method, ...$args)
    {
        return get_called_class() . '%' . $method . '%' . md5(serialize([
            $this->take,
            $this->with,
            $this->wheres,
            $this->whereIns,
            $this->orderBys,
            $this->scopes,
            $this->extraQueryParams,
            $this->perPage,
            $this->minPerPage,
            $this->maxPerPage,
            $args
        ]));
    }

    protected function allowCacheFor(string $method)
    {
        if ($this->getCacheOff()) {
            return false;
        }
        if (!empty($this->getCacheOnly()) && !in_array($method, $this->getCacheOnly())) {
            return false;
        }
        if (in_array($method, $this->getCacheExcept())) {
            return false;
        }
        return true;
    }

    protected function cache(string $method, callable $func, ...$args)
    {
        if (!$this->allowCacheFor($method)) {
            return $func();
        }
        $cacheTimeForMethod = $this->getCacheTimeForMethod($method);

        return $this->getCacheRepo()->remember($this->cacheKeyName($method, ...$args), $cacheTimeForMethod, $func);
    }

    public function all(array $columns = ['*'])
    {
        return $this->cache(__FUNCTION__, function () use ($columns) {
            return parent::all($columns);
        }, $columns);
    }

    public function get(array $columns = ['*'])
    {
        return $this->cache(__FUNCTION__, function () use ($columns) {
            return parent::get($columns);
        }, $columns);
    }

    public function paginate($perPage = 15, array $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->cache(__FUNCTION__, function () use ($perPage, $columns, $pageName, $page) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }, $perPage, $columns, $pageName, $page);
    }

    public function simplePaginate($perPage = 15, array $columns = ['*'])
    {
        return $this->cache(__FUNCTION__, function () use ($perPage, $columns) {
            return parent::simplePaginate($perPage, $columns);
        }, $perPage, $columns);
    }

    public function count(): int
    {
        return $this->cache(__FUNCTION__, function () {
            return parent::count();
        });
    }

    public function find($id, array $columns = ['*'], string $column = 'id')
    {
        return $this->cache(__FUNCTION__, function () use ($id, $columns, $column) {
            return parent::find($id, $columns, $column);
        }, $id, $columns, $column);
    }

    public function findOrFail($id, array $columns = ['*'], string $column = 'id')
    {
        return $this->cache(__FUNCTION__, function () use ($id, $columns, $column) {
            return parent::findOrFail($id, $columns, $column);
        }, $id, $columns, $column);
    }

    public function first(array $columns = ['*'])
    {
        return $this->cache(__FUNCTION__, function () use ($columns) {
            return parent::first($columns);
        }, $columns);
    }

    public function firstOrFail(array $columns = ['*'])
    {
        return $this->cache(__FUNCTION__, function () use ($columns) {
            return parent::firstOrFail($columns);
        }, $columns);
    }
}
