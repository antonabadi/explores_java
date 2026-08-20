<?php

require_once __DIR__ . '/../core/Model.php';

class Tour extends Model
{
    protected string $table = 'tours';

    protected array $fillable = [
        'destination_id',
        'package_id',
        'title',
        'slug',
        'duration_days',
        'duration_nights',
        'price',
        'group_type',
        'description',
        'itinerary',
        'facility_included',
        'facility_excluded',
        'is_active',
    ];

    public function findBySlug(string $slug): array|false
    {
        return $this->findBy('slug', $slug);
    }

    /** Full tour detail with destination + package names joined in */
    public function findWithRelations(int $id): array|false
    {
        $sql = "SELECT t.*, d.name AS destination_name, d.slug AS destination_slug,
                       p.package_name
                FROM {$this->table} t
                JOIN destinations d ON d.id = t.destination_id
                JOIN tour_packages p ON p.id = t.package_id
                WHERE t.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $tour = $stmt->fetch();

        if ($tour) {
            $tour['images'] = $this->getImages($id);
        }

        return $tour;
    }

    public function findBySlugWithRelations(string $slug): array|false
    {
        $sql = "SELECT t.*, d.name AS destination_name, d.slug AS destination_slug,
                       p.package_name
                FROM {$this->table} t
                JOIN destinations d ON d.id = t.destination_id
                JOIN tour_packages p ON p.id = t.package_id
                WHERE t.slug = :slug
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $tour = $stmt->fetch();

        if ($tour) {
            $tour['images'] = $this->getImages((int) $tour['id']);
        }

        return $tour;
    }

    public function getImages(int $tourId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tour_images WHERE tour_id = :tour_id ORDER BY id ASC"
        );
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll();
    }

    /**
     * Filtered + paginated search across active tours.
     * $filters keys: destination_id, package_id, group_type, min_price, max_price,
     *                min_duration, max_duration, keyword
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where = ['t.is_active = 1'];
        $params = [];

        if (!empty($filters['destination_id'])) {
            $where[] = 't.destination_id = :destination_id';
            $params['destination_id'] = $filters['destination_id'];
        }

        if (!empty($filters['package_id'])) {
            $where[] = 't.package_id = :package_id';
            $params['package_id'] = $filters['package_id'];
        }

        if (!empty($filters['group_type'])) {
            $where[] = 't.group_type = :group_type';
            $params['group_type'] = $filters['group_type'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = 't.price >= :min_price';
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = 't.price <= :max_price';
            $params['max_price'] = $filters['max_price'];
        }

        if (!empty($filters['min_duration'])) {
            $where[] = 't.duration_days >= :min_duration';
            $params['min_duration'] = $filters['min_duration'];
        }

        if (!empty($filters['max_duration'])) {
            $where[] = 't.duration_days <= :max_duration';
            $params['max_duration'] = $filters['max_duration'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = 'MATCH(t.title, t.description) AGAINST (:keyword IN NATURAL LANGUAGE MODE)';
            $params['keyword'] = $filters['keyword'];
        }

        $whereSql = implode(' AND ', $where);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT t.*, d.name AS destination_name, p.package_name
                FROM {$this->table} t
                JOIN destinations d ON d.id = t.destination_id
                JOIN tour_packages p ON p.id = t.package_id
                WHERE {$whereSql}
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) AS total FROM {$this->table} t WHERE {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['total'];

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public function addImage(int $tourId, string $imagePath): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tour_images (tour_id, image_path) VALUES (:tour_id, :image_path)"
        );
        $stmt->execute(['tour_id' => $tourId, 'image_path' => $imagePath]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteImage(int $imageId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tour_images WHERE id = :id");
        return $stmt->execute(['id' => $imageId]);
    }

    public function generateUniqueSlug(string $title, int $ignoreId = null): string
    {
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
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
