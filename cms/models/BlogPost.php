<?php

require_once __DIR__ . '/../core/Model.php';

class BlogPost extends Model
{
    protected string $table = 'blog_posts';

    protected array $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
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
}
