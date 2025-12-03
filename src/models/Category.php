<?php

class Category
{
    public $id;
    public $name;
    public $description;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->status = isset($data['status']) ? (int)$data['status'] : 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }
}

