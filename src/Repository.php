<?php

namespace NathanDunn\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class Repository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $helpers = [
            'getPaginated' => ['paginate'],
            'getCount' => ['count'],
            'getSortedAndPaginated' => ['sortable', 'paginate'],
            'getSorted' => ['sortable'],
            'get' => ['get'],
            'firstOrFail' => ['firstOrFail'],
            'first' => ['first'],
            'exists' => ['exists'],
        ];

        foreach ($helpers as $helper => $helperMethods) {
            if (Str::startsWith($method, $helper)) {
                $restOfMethod = ucfirst(Str::replaceFirst($helper, '', $method));

                if (method_exists($this, $restOfMethod)) {
                    $helperMethods = collect($helperMethods);

                    return $helperMethods->reduce(function ($carry, $item) use ($args) {
                        return $carry->$item();
                    }, call_user_func_array([$this, $restOfMethod], $args));
                }
            }
        }

        return call_user_func_array([$this->model, $method], $args);
    }

    public function fromId($id): Builder
    {
        return $this->model->where('id', '=', $id);
    }
}