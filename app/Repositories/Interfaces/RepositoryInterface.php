<?php
namespace App\Repositories\Interfaces;

interface RepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []);
    public function paginate(int $perPage = 25, array $columns = ['*'], array $relations = []);
    public function find(int $id, array $columns = ['*'], array $relations = []);
    public function findBy(array $conditions, array $columns = ['*'], array $relations = []);
    public function create(array $attributes);
    public function update(int $id, array $attributes);
    public function delete(int $id);
    public function getModel();
}