<?php

namespace Fomvasss\Repository\Http\Middleware;

use Closure;

class RepositoryCacheOff
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['repository.cache.off' => true]);

        return $next($request);
    }
}
