# Laravel Model Repositories

A lightweight abstraction for Laravel Eloquent models that enables cleaner, more expressive repository-style access to your application's data layer.

> "Fetching collections or business-specific queries from models violates separation of concerns. This package provides an elegant way to wrap Eloquent models into repository-like entities."

Inspired by [Jack Wagstaffe](https://github.com/jackowagstaffe)

---

## 🚀 Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require nathandunn/model-repositories
```

---

## 📦 What This Provides

This package allows you to:

- Encapsulate complex query logic outside your Eloquent models.
- Maintain proper separation between models and querying logic.
- Leverage dynamic method chaining like `getPaginatedForUser()` or `getForXyz()`.

---

## 🧠 Concept

Instead of injecting Eloquent models directly into services or controllers, create a repository class per model that encapsulates query logic.

For example, instead of:

```php
$records = Record::where('user_id', $user->id)->paginate();
```

You can use:

```php
$records = $recordRepository->getPaginatedForUser($user);
```

---

## 🛠 Usage

### 1. Create a Custom Repository

```php
<?php

namespace App\Records;

use App\Users\User;
use Illuminate\Database\Eloquent\Builder;
use NathanDunn\Repositories\Repository;

class RecordRepository extends Repository
{
    public function __construct(Record $record)
    {
        parent::__construct($record);
    }

    public function forUser(User $user): Builder
    {
        return $this->model->where('user_id', '=', $user->id);
    }
}
```

---

### 2. Inject into Your Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecordResource;
use App\Records\RecordRepository;
use Illuminate\Http\Request;

class RecordsController extends Controller
{
    public function __construct(
        protected RecordRepository $recordRepository
    ) {}

    public function index(Request $request)
    {
        $records = $this->recordRepository->getPaginatedForUser($request->user());

        return RecordResource::collection($records);
    }
}
```

---

## 🔁 Dynamic Method Chaining

The base `Repository` class supports auto-chaining based on naming convention. The following helper prefixes are recognised:

| Prefix                  | Description                |
| ----------------------- | -------------------------- |
| `get`                   | Fetch a collection         |
| `getPaginated`          | Fetch and paginate         |
| `getCount`              | Get count of query results |
| `first` / `firstOrFail` | Fetch a single item        |
| `exists`                | Check for existence        |

So calling `getPaginatedForUser($user)` will:

1. Call `forUser($user)` (your custom method)
2. Then call `paginate()` on the result

This is handled by `__call()` in the base class using a naming convention-based resolver.

---

## 🧪 Example Call Chain

Given a repository method `forUser(User $user): Builder`, these dynamic calls become possible:

```php
$repo->getForUser($user);              // ->forUser()->get()
$repo->getPaginatedForUser($user);     // ->forUser()->paginate()
$repo->getCountForUser($user);         // ->forUser()->count()
```

---

## ✅ Requirements

- Laravel 10+
- PHP 8.1+

---

## 📚 License

MIT © [Nathan Dunn](https://github.com/nthndnn)
