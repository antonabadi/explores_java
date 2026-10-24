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
        'location_map',
    ];

    public function findBySlug(string $slug): array|false
    {
        return $this->findBy('slug', $slug);
    }

    /** Destination list with tour count */
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

    /**
     * Destination detail lengkap beserta areas, tags, dan tour count.
     * Blog suggestions dimuat terpisah via getBlogSuggestions().
     */
    public function withRelations(int $id): array|false
    {
        $sql = "SELECT d.*, COUNT(DISTINCT t.id) AS tour_count
                FROM {$this->table} d
                LEFT JOIN tours t ON t.destination_id = d.id AND t.is_active = 1
                WHERE d.id = :id
                GROUP BY d.id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $destination = $stmt->fetch();

        if (!$destination) {
            return false;
        }

        $destination['areas'] = $this->getAreas($id);
        $destination['tags']  = $this->getTags($id);

        return $destination;
    }

    /**
     * Destination detail by slug beserta areas dan tags.
     */
    public function withRelationsBySlug(string $slug): array|false
    {
        $sql = "SELECT d.*, COUNT(DISTINCT t.id) AS tour_count
                FROM {$this->table} d
                LEFT JOIN tours t ON t.destination_id = d.id AND t.is_active = 1
                WHERE d.slug = :slug
                GROUP BY d.id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $destination = $stmt->fetch();

        if (!$destination) {
            return false;
        }

        $destination['areas'] = $this->getAreas((int) $destination['id']);
        $destination['tags']  = $this->getTags((int) $destination['id']);

        return $destination;
    }

    /** Ambil areas yang terhubung ke destinasi */
    public function getAreas(int $destinationId): array
    {
        $sql = "SELECT a.id, a.name, a.slug, a.description
                FROM destination_areas a
                JOIN destination_area_map dam ON dam.area_id = a.id
                WHERE dam.destination_id = :destination_id
                ORDER BY a.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['destination_id' => $destinationId]);
        return $stmt->fetchAll();
    }

    /** Ambil activity tags yang terhubung ke destinasi */
    public function getTags(int $destinationId): array
    {
        $sql = "SELECT dt.id, dt.name, dt.slug, dt.icon
                FROM destination_tags dt
                JOIN destination_tag_map dtm ON dtm.tag_id = dt.id
                WHERE dtm.destination_id = :destination_id
                ORDER BY dt.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['destination_id' => $destinationId]);
        return $stmt->fetchAll();
    }

    /**
     * Blog suggestions untuk satu destinasi.
     *
     * Strategi:
     * 1. Ambil post yang di-assign manual via destination_blog_posts, urut sort_order.
     * 2. Jika kurang dari $limit, fallback: cari published blog_posts yang blog_tags-nya
     *    slug-nya cocok dengan destination_tags slug destinasi ini.
     * 3. Merge, dedup by post id, potong sesuai $limit.
     */
    public function getBlogSuggestions(int $destinationId, int $limit = 6): array
    {
        // 1. Manual suggestions
        $sqlManual = "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image,
                             p.published_at, p.reading_time,
                             c.name AS category_name, c.slug AS category_slug
                      FROM destination_blog_posts dbp
                      JOIN blog_posts p ON p.id = dbp.post_id AND p.status = 'published'
                      LEFT JOIN blog_categories c ON c.id = p.category_id
                      WHERE dbp.destination_id = :destination_id
                      ORDER BY dbp.sort_order ASC, p.published_at DESC
                      LIMIT :limit";
        $stmt = $this->db->prepare($sqlManual);
        $stmt->bindValue(':destination_id', $destinationId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $manual = $stmt->fetchAll();

        if (count($manual) >= $limit) {
            return $manual;
        }

        // 2. Auto fallback via tag overlap (blog_tags.slug ↔ destination_tags.slug)
        $excludeIds = array_column($manual, 'id');
        $remaining  = $limit - count($manual);

        $notInClause = '';
        if (!empty($excludeIds)) {
            $notInClause = 'AND p.id NOT IN (' . implode(',', array_map('intval', $excludeIds)) . ')';
        }

        $sqlAuto = "SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.featured_image,
                           p.published_at, p.reading_time,
                           c.name AS category_name, c.slug AS category_slug
                    FROM blog_posts p
                    JOIN blog_post_tags bpt ON bpt.post_id = p.id
                    JOIN blog_tags bt ON bt.id = bpt.tag_id
                    JOIN destination_tags dt ON dt.slug = bt.slug
                    JOIN destination_tag_map dtm ON dtm.tag_id = dt.id
                    LEFT JOIN blog_categories c ON c.id = p.category_id
                    WHERE dtm.destination_id = :destination_id
                      AND p.status = 'published'
                      {$notInClause}
                    ORDER BY p.published_at DESC
                    LIMIT :limit";
        $stmt = $this->db->prepare($sqlAuto);
        $stmt->bindValue(':destination_id', $destinationId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $remaining, PDO::PARAM_INT);
        $stmt->execute();
        $auto = $stmt->fetchAll();

        return array_merge($manual, $auto);
    }

    /**
     * Filter + paginate destinations.
     *
     * $filters keys: area (slug), tag (slug atau array slug), keyword, page, per_page
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $join   = '';
        $where  = [];
        $params = [];

        if (!empty($filters['area'])) {
            $join .= " JOIN destination_area_map dam ON dam.destination_id = d.id
                       JOIN destination_areas da ON da.id = dam.area_id";
            $where[] = 'da.slug = :area_slug';
            $params['area_slug'] = $filters['area'];
        }

        if (!empty($filters['tag'])) {
            $tags = is_array($filters['tag']) ? $filters['tag'] : explode(',', $filters['tag']);
            $tags = array_map('trim', array_filter($tags));

            if (!empty($tags)) {
                // Subquery: destinasi yang memiliki SEMUA tag yang diminta (AND logic)
                // Atau bisa pakai OR logic (ANY) — implementasi di sini menggunakan ANY
                $join .= " JOIN destination_tag_map dtm ON dtm.destination_id = d.id
                           JOIN destination_tags dta ON dta.id = dtm.tag_id";
                $placeholders = [];
                foreach ($tags as $idx => $tagSlug) {
                    $key = "tag_slug_{$idx}";
                    $placeholders[] = ":{$key}";
                    $params[$key] = $tagSlug;
                }
                $where[] = 'dta.slug IN (' . implode(', ', $placeholders) . ')';
            }
        }

        if (!empty($filters['keyword'])) {
            $where[] = '(d.name LIKE :keyword OR d.description LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $page     = max(1, $page);
        $offset   = ($page - 1) * $perPage;

        $sql = "SELECT DISTINCT d.*, COUNT(DISTINCT t.id) AS tour_count
                FROM {$this->table} d
                LEFT JOIN tours t ON t.destination_id = d.id AND t.is_active = 1
                {$join}
                {$whereSql}
                GROUP BY d.id
                ORDER BY d.name ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(DISTINCT d.id) AS total
                     FROM {$this->table} d
                     {$join}
                     {$whereSql}";
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

    /** Sinkronisasi pivot destination_area_map */
    public function syncAreas(int $destinationId, array $areaIds): void
    {
        $this->db->prepare(
            "DELETE FROM destination_area_map WHERE destination_id = :id"
        )->execute(['id' => $destinationId]);

        if (empty($areaIds)) {
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO destination_area_map (destination_id, area_id) VALUES (:destination_id, :area_id)"
        );
        foreach ($areaIds as $areaId) {
            $stmt->execute(['destination_id' => $destinationId, 'area_id' => (int) $areaId]);
        }
    }

    /** Sinkronisasi pivot destination_tag_map */
    public function syncTags(int $destinationId, array $tagIds): void
    {
        $this->db->prepare(
            "DELETE FROM destination_tag_map WHERE destination_id = :id"
        )->execute(['id' => $destinationId]);

        if (empty($tagIds)) {
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO destination_tag_map (destination_id, tag_id) VALUES (:destination_id, :tag_id)"
        );
        foreach ($tagIds as $tagId) {
            $stmt->execute(['destination_id' => $destinationId, 'tag_id' => (int) $tagId]);
        }
    }

    /**
     * Sinkronisasi pivot destination_blog_posts (manual blog suggestions).
     * $posts: array of ['post_id' => int, 'sort_order' => int]
     *         atau array of int (post_id saja, sort_order otomatis dari index)
     */
    public function syncBlogPosts(int $destinationId, array $posts): void
    {
        $this->db->prepare(
            "DELETE FROM destination_blog_posts WHERE destination_id = :id"
        )->execute(['id' => $destinationId]);

        if (empty($posts)) {
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO destination_blog_posts (destination_id, post_id, sort_order)
             VALUES (:destination_id, :post_id, :sort_order)"
        );

        foreach ($posts as $idx => $item) {
            if (is_array($item)) {
                $postId    = (int) $item['post_id'];
                $sortOrder = (int) ($item['sort_order'] ?? $idx);
            } else {
                $postId    = (int) $item;
                $sortOrder = $idx;
            }
            $stmt->execute([
                'destination_id' => $destinationId,
                'post_id'        => $postId,
                'sort_order'     => $sortOrder,
            ]);
        }
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
