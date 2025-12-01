<?php

class Guide
{
    public $id;
    public $full_name;
    public $dob;
    public $photo;
    public $contact;
    public $certificates;
    public $languages;
    public $experience;
    public $tour_history;
    public $rating;
    public $health_status;
    public $group; // 'noidia' or 'quocte'
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->full_name = $data['full_name'] ?? '';
        $this->dob = $data['dob'] ?? null;
        $this->photo = $data['photo'] ?? null;
        $this->contact = $data['contact'] ?? '';
        $this->certificates = $data['certificates'] ?? '';
        $this->languages = $data['languages'] ?? '';
        $this->experience = $data['experience'] ?? '';
        $this->tour_history = $data['tour_history'] ?? '';
        $this->rating = isset($data['rating']) ? (float)$data['rating'] : null;
        $this->health_status = $data['health_status'] ?? '';
        $this->group = $data['group'] ?? 'noidia';
        $this->status = isset($data['status']) ? (int)$data['status'] : 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
