<?php

require_once __DIR__ . '/../core/Model.php';

class Testimonial extends Model
{
    protected string $table = 'testimonials';

    protected array $fillable = [
        'tour_id',
        'customer_name',
        'customer_email',
        'rating',
        'review_text',
        'customer_photo',
        'is_approved',
    ];

    public function approved(int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_approved = 1 ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function pending(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE is_approved = 0 ORDER BY created_at ASC"
        );
        return $stmt->fetchAll();
    }

    public function forTour(int $tourId, bool $approvedOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE tour_id = :tour_id";
        if ($approvedOnly) {
            $sql .= " AND is_approved = 1";
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll();
    }

    public function approve(int $id): bool
    {
        return $this->update($id, ['is_approved' => 1]);
    }

    public function reject(int $id): bool
    {
        return $this->update($id, ['is_approved' => 0]);
    }

    public function averageRatingForTour(int $tourId): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(rating) AS avg_rating FROM {$this->table}
             WHERE tour_id = :tour_id AND is_approved = 1"
        );
        $stmt->execute(['tour_id' => $tourId]);
        $result = $stmt->fetch()['avg_rating'];
        return $result !== null ? round((float) $result, 1) : 0.0;
    }
}
