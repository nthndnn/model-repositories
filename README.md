# Laravel Model Respositories
Wraps around [Eloquent models](https://laravel.com/docs/10.x/eloquent) allowing them to be used as a repository-like entity. 

## Why?
Because getting a collection from a model "object" isn't really a models
role, and is deceptive when injecting a model and using it in another context

Inspired by [Jack Wagstaffe](https://github.com/jackowagstaffe)

## Install
You can install the package with [Composer](https://getcomposer.org/) by running the following command:

```
composer require nathandunn/model-repositories
```

## Usage instructions
Below is an example of an example repository for a `Record` model. This extends the base `Repository` class and adds a custom `forUser` method.

```php
<?php

namespace App\Records;

use App\Users\User;
use Illuminate\Database\Eloquent\Builder;
use NathanDunn\Repositories\Repository;

class RecordRepository extends Repository
{
    /**
     * @param Record $record
     */
    public function __construct(Record $record)
    {
        parent::__construct($record);
    }

    public function forUser(User $user): Builder
    {
      return $this->model->where('user_id', '=', $uuid);
    }
}
```

You can inject the `RecordRepository` in a controller:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecordResource;
use App\Records\RecordRepository;
use Illuminate\Http\Request;

class RecordsController extends Controller
{
    protected RecordRepository $recordRepository;
    
    public function __construct(RecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $records = $this->recordRepository->getForUser($user);
        
        return RecordResource::collection($records);
    }
}
```