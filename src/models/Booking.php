<?php

class Booking
{
    public $id;
    public $tour_id;
    public $created_by;
    public $assigned_guide_id;
    public $status;
    public $start_date;
    public $end_date;
    public $schedule_detail;
    public $service_detail;
    public $diary;
    public $lists_file;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->tour_id = $data['tour_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->assigned_guide_id = $data['assigned_guide_id'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->start_date = $data['start_date'] ?? null;
        $this->end_date = $data['end_date'] ?? null;
        $this->schedule_detail = $data['schedule_detail'] ?? null;
        $this->service_detail = $data['service_detail'] ?? null;
        $this->diary = $data['diary'] ?? null;
        $this->lists_file = $data['lists_file'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function hasGuide(): bool
    {
        return !empty($this->assigned_guide_id);
    }

    public function getStatusName($pdo = null)
    {
        if (!$this->status || !$pdo) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT name FROM tour_statuses WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $this->status]);
            $result = $stmt->fetch();
            return $result ? $result['name'] : null;
        } catch (PDOException $e) {
            error_log('Get status name failed: ' . $e->getMessage());
            return null;
        }
    }
}

