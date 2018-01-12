# Laravel repository
Base class and methods for build repository pattern in Laravel

## Installation
Run:
```bash
	composer require fomvasss/laravel-repository
```

## Methods repository

Interface all methods see in [RepositoryInterface](src/Contracts/RepositoryInterface.php)
Realisation eloquent all methods see in [BaseRepository](src/Eloquent/BaseRepository.php)

---
## Usage

### Make own repository

Extend your repository class the next `BaseRepository` class
```php
<?php
namespace App\Repositories;

use Fomvasss\Repository\Eloquent\BaseRepository;

class ArticleRepository extends BaseRepository {

    /**
     * Specify Model class name
     * @return string
     */
    function model()
    {
        return "App\\Article";
    }
}
```
### Use repository methods
```php
<?php
namespace App\Http\Controllers;

use App\Repositories\ArticleRepository;

class ArticleController extends BaseController {

    /**
     * @var ArticleRepository
     */
    protected $repository;

    public function __construct(ArticleRepository $repository) {
        $this->repository = $repository;
    }
    
    public function index() {
        $articles = $this->repository->all();
        //....
    }
    //....
}
```

## Events repository
Repository have next events:
- RepositoryEntityCreated
- RepositoryEntityUpdated
- RepositoryEntityDeleted
