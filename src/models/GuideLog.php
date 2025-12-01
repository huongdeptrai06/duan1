<?php

class GuideLog
{
    public $id;
    public $guide_id;
    public $changed_by; // user id
    public $change_data; // json string
    public $created_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->guide_id = $data['guide_id'] ?? null;
        $this->changed_by = $data['changed_by'] ?? null;
        $this->change_data = $data['change_data'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }
}

