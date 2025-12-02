<?php

class Tour
{
    public $id;
    public $name;
    public $description;
    public $category_id;
    public $schedule;
    public $images;
    public $prices;
    public $policies;
    public $suppliers;
    public $price;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->schedule = $data['schedule'] ?? null;
        $this->images = $data['images'] ?? null;
        $this->prices = $data['prices'] ?? null;
        $this->policies = $data['policies'] ?? null;
        $this->suppliers = $data['suppliers'] ?? null;
        $this->price = isset($data['price']) ? (float)$data['price'] : null;
        $this->status = isset($data['status']) ? (int)$data['status'] : 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function getCategoryName($pdo = null)
    {
        if (!$this->category_id || !$pdo) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $this->category_id]);
            $result = $stmt->fetch();
            return $result ? $result['name'] : null;
        } catch (PDOException $e) {
            error_log('Get category name failed: ' . $e->getMessage());
            return null;
        }
    }
}

