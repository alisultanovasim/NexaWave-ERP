<?php

namespace App\Repositories;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;

//plz dont touch ill work on

class CrudRepository {

    use ValidatesRequests;

    private $model;
    private $with = [];
    private $fillable = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function get(){
        return $this->model->with($this->with)->get();
    }

    public function show($id){
        return $this->model->where('id', $id)->with($this->with)->firstOrFail();
    }

    public function paginate($perPage = null){
        $this->validate(request(), [
            'per_page' => 'nullable|integer'
        ]);
        return $this->model->with($this->with)->paginate($perPage ?? request()->get('per_page'));
    }


    public function save(): bool {
        return $this->model->fill($this->fillable)->save();
    }

    /**
     * @param $id
     * @return bool
     */
    public function destroy($id): bool {
        $item = $this->model->where('id', $id)->with($this->with)->firstOrFail();
        return $item->delete();
    }

    /**
     * @param array $fillable
     */
    public function setFillable(array $fillable = []){
        $this->fillable = $fillable;
    }


    /**
     * @return Builder
     */
    public function model(): Builder{
        return $this->model->query();
    }

    /**
     * @param Builder $builder
     */
    public function build(Builder $builder): void {
        $this->model = $builder;
    }

}
