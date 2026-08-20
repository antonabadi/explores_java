<?php

require_once __DIR__ . '/../core/Model.php';

class Destination extends Model
{
    protected string $table = 'destinations';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'image_thumbnail',
    ];

    public function findBySlug(string $slug): array|false
    {
        return $this->findBy('slug', $slug);
    }

    /** Destination with its tour count */
    public function withTourCount(): array
    {
        $sql = "SELECT d.*, COUNT(t.id) AS tour_count
                FROM {$this->table} d
                LEFT JOIN tours t ON t.destination_id = d.id AND t.is_active = 1
                GROUP BY d.id
                ORDER BY d.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function generateUniqueSlug(string $name, int $ignoreId = null): string
    {
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $slug = $baseSlug;
        $i = 1;

        while (true) {
            $sql = "SELECT id FROM {$this->table} WHERE slug = :slug";
            $params = ['slug' => $slug];

            if ($ignoreId !== null) {
                $sql .= " AND id != :id";
                $params['id'] = $ignoreId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if (!$stmt->fetch()) {
                break;
            }

            $slug = $baseSlug . '-' . (++$i);
        }

        return $slug;
    }
}
