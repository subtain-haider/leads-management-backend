<?php
namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 25, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->select($columns)->paginate($perPage);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->select($columns)->with($relations)->findOrFail($id);
    }

    public function findBy(array $conditions, array $columns = ['*'], array $relations = [])
    {
        return $this->model->select($columns)->with($relations)->where($conditions)->get();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(int $id, array $attributes)
    {
        $record = $this->find($id);
        $record->update($attributes);
        return $record;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    public function getModel()
    {
        return $this->model;
    }
}