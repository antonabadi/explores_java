<?php

require_once __DIR__ . '/../core/Model.php';

class BlogPost extends Model
{
    protected string $table = 'blog_posts';

    protected array $fillable = [
        'title',
        'meta_title',
        'meta_description',
        'slug',
        'canonical_url',
        'content',
        'excerpt',
        'featured_image',
        'og_image',
        'reading_time',
        'status',
        'author_id',
        'category_id',
        'view_count',
        'published_at',
    ];

    public function findBySlug(string $slug): array|false
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Fetch published posts with category and author relations joined
     */
    public function getPublished(int $limit = 6): array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                       u.display_name AS author_name, u.avatar AS author_avatar
                FROM {$this->table} p
                LEFT JOIN blog_categories c ON c.id = p.category_id
                LEFT JOIN blog_users u ON u.id = p.author_id
                WHERE p.status = 'published'
                ORDER BY p.published_at DESC, p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Fetch paginated published posts with category and author relations
     */
    public function getPaginatedPublished(int $page = 1, int $perPage = 6): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                       u.display_name AS author_name, u.avatar AS author_avatar
                FROM {$this->table} p
                LEFT JOIN blog_categories c ON c.id = p.category_id
                LEFT JOIN blog_users u ON u.id = p.author_id
                WHERE p.status = 'published'
                ORDER BY p.published_at DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE status = 'published'";
        $total = (int) $this->db->query($countSql)->fetch()['total'];

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
