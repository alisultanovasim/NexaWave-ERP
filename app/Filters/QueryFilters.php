<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueryFilters
{
    protected $request;
    protected $builder;

    /**
     * QueryFilters constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filled filters
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $name => $value) {
            $originalName = $name;
            $camelCaseName = Str::camel($name);
            if ( ! method_exists($this, $camelCaseName)) {
                continue;
            }
            if (!is_null($this->request->get($originalName)) and $this->request->get($originalName) !== '') {
                $this->$camelCaseName($value);
            }
        }

        return $this->builder;
    }

    /**
     * Get filters
     * @return array
     */
    public function filters()
    {
        return $this->request->all();
    }


    /**
     * @param $name
     * @param null $value
     */
    public function addFilter($name, $value = null){
        $this->request->merge([$name => $value]);
    }

    /**
     * Remove all filters from request
     */
    public function flushAllFilters(){
        $this->request = new Request();
    }
}
