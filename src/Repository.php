<?php

namespace NathanDunn\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Base repository class that provides dynamic method chaining for Eloquent models.
 * 
 * @template TModel of Model
 * 
 * @method Builder<TModel> getPaginatedFor*(mixed ...$args) Get paginated results for a custom method
 * @method int getCountFor*(mixed ...$args) Get count of results for a custom method
 * @method Builder<TModel> getFor*(mixed ...$args) Get collection for a custom method
 * @method TModel|null firstOrFailFor*(mixed ...$args) Get first result or fail for a custom method
 * @method TModel|null firstFor*(mixed ...$args) Get first result for a custom method
 * @method bool existsFor*(mixed ...$args) Check existence for a custom method
 */
abstract class Repository
{
    /**
     * The Eloquent model instance.
     * 
     * @var TModel
     */
    protected Model $model;

    /**
     * Create a new repository instance.
     * 
     * @param TModel $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Handle dynamic method calls.
     * 
     * Supports method chaining with prefixes like 'getPaginated', 'getCount', etc.
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $helpers = [
            'getPaginated' => ['paginate'],
            'getCount' => ['count'],
            'get' => ['get'],
            'firstOrFail' => ['firstOrFail'],
            'first' => ['first'],
            'exists' => ['exists'],
        ];

        foreach ($helpers as $helper => $helperMethods) {
            if (Str::startsWith($method, $helper)) {
                $restOfMethod = ucfirst(Str::replaceFirst($helper, '', $method));

                if (method_exists($this, $restOfMethod)) {
                    /** @var Collection $helperMethods */
                    $helperMethods = collect($helperMethods);

                    return $helperMethods->reduce(function ($carry, $item) use ($args) {
                        return $carry->$item();
                    }, call_user_func_array([$this, $restOfMethod], $args));
                }
            }
        }

        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Get a query builder for a model by ID.
     * 
     * @param int|string $id
     * @return Builder<TModel>
     */
    public function forId(int|string $id): Builder
    {
        return $this->model->where($this->model->getKeyName(), '=', $id);
    }
}