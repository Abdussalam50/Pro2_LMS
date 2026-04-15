<?php

namespace App\Services;

class BaseCrudService
{
    /**
     * Get all records for a model, optionally filtering and eager loading.
     */
    public function getAll($modelClass, $conditions = [], $relations = [], $orderBy = ['created_at', 'desc'])
    {
        $query = $modelClass::with($relations);
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        if (!empty($orderBy)) {
            $query->orderBy($orderBy[0], $orderBy[1]);
        }

        return $query->get();
    }

    /**
     * Create a new record
     */
    public function create($modelClass, $data)
    {
        return $modelClass::create($data);
    }

    /**
     * Update an existing record
     */
    public function update($modelClass, $id, $data)
    {
        $record = $modelClass::find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return false;
    }

    /**
     * Delete a record
     */
    public function delete($modelClass, $id)
    {
        $record = $modelClass::find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
}
