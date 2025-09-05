<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasBaseRepository
{
    protected string $model;

    protected function newModel(): Model
    {
        return new $this->model;
    }

    public function all(): Collection
    {
        return $this->newModel()->all();
    }

    public function find(int $id): ?Model
    {
        return $this->newModel()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->newModel()->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->find($id);

        return $model && $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);

        return $model ? $model->delete() : false;
    }
}
