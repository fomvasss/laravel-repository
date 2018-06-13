# Laravel repository

[![Latest Stable Version](https://poser.pugx.org/fomvasss/laravel-repository/v/stable)](https://packagist.org/packages/fomvasss/laravel-repository)
[![Total Downloads](https://poser.pugx.org/fomvasss/laravel-repository/downloads)](https://packagist.org/packages/fomvasss/laravel-repository)
[![Latest Unstable Version](https://poser.pugx.org/fomvasss/laravel-repository/v/unstable)](https://packagist.org/packages/fomvasss/laravel-repository)
[![License](https://poser.pugx.org/fomvasss/laravel-repository/license)](https://packagist.org/packages/fomvasss/laravel-repository)

Base class and methods for build repository pattern in Laravel and cache queries

## Installation
Run:
```bash
	composer require fomvasss/laravel-repository
```
Publish config:
```bash
php artisan vendor:publish --provider="Fomvasss\Repository\Providers\RepositoryServiceProvider" --tag="repository-config"
```

## All base methods repository

- Interface all methods see in [RepositoryInterface](src/Contracts/RepositoryInterface.php)
- Realisation interface see in [BaseRepository](src/Eloquent/BaseRepository.php)

## Usage

### Make own repository
Extend your repository class the next `BaseRepository` class
```php
<?php

namespace App\Repositories;

use App\Models\Article;
use Fomvasss\Repository\Eloquent\BaseRepository;

class ArticleRepository extends BaseRepository
{
    public function model()
    {
        return Article::class;
    }
    //...
}

```

### Use repository methods
```php
<?php
namespace App\Http\Controllers;

use App\Repositories\ArticleRepository;

class ArticleController extends BaseController {

    protected $repository;

    public function __construct(ArticleRepository $repository) {
        $this->repository = $repository;
    }
    
    public function index() {
		$articles = $this->repository
			->scopes(['byStatus', 1], ['sortable', ['id'=>'desc']], 'searchable')
			->with('user')
			->where('created_at', \Carbon\Carbon::yesterday(), '>')
			->orderBy('created_at')
			->get();

        //....
    }
    
        public function show() {
    		$article = $this->repository
    			->scope('byStatus', 1)
    			->with(['user', 'categories'])
    			->where('created_at', \Carbon\Carbon::today(), '<')
    			->orderBy('created_at')
    			->firstOrFail();
    
            //....
        }
    //....
}
```

### Make custom method in own repository

__! Custom method do not use repository cache!__
```php
    public function myCustomMethodByType($attributes)
    {
        $this->applyExtras();
        $models = $this->query;

        if (!empty($attributes['type'])) {
            $models = $this->query->where('type', $attributes['type']);
        }

        $this->unsetClauses();
        return $models;
    }
```

## Events repository

Repository entity have next events:
- `RepositoryEntityCreated`
- `RepositoryEntityUpdated`
- `RepositoryEntityDeleted`

For example, you can add in your EventServiceProvider next:
```php
protected $listen = [
	\Fomvasss\Repository\Events\RepositoryEntityCreated::class => [
		\App\Listeners\CreatedNewModelInRepoListener::class
	]
];
```
And use next method in method handle (in your listener `CreatedNewModeInRepoListener`):

```php
public function handle(RepositoryEntityCreated $event)
{
	dd($event->getAction());
	dd($event->getModel());
	dd($event->getRepository());
}
```


## Usage repository cache

All cache methods see in Interface [CacheableInterface](src/Contracts/CacheableInterface.php)

Example repository with cache:

```php
<?php

namespace App\Repositories;

use App\Models\Article;
use Fomvasss\Repository\Contracts\CacheableInterface;
use Fomvasss\Repository\Eloquent\BaseRepository;
use Fomvasss\Repository\Traits\CacheableRepository;

class ArticleRepository extends BaseRepository implements CacheableInterface
{
    use CacheableRepository;

	protected $cacheTime = 60;
	
    protected $cacheTimeForMethod = [
        'all' => 10,
        'get' => 10,
        'paginate' => 10,
        'find' => 1,
    ];
    
    protected $cacheOnly = ['all', 'get', 'find'];

    public function model()
    {
        return Article::class;
    }
}
```

### Middleware for off cache

Example usage middleware in routes:

Add to `App\Http\Kernel.php`
```php
protected $routeMiddleware = [
	//...
	'rpc-off' => \Fomvasss\Repository\Http\Middleware\RepositoryCacheOff::class,
];
```
and use in your routes:
```php
Route::get('article', ArticleController@index)->middleware(['rpc-off']);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
