<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

class Asset extends Model
{
    protected string $table = 'media_assets';

    protected array $fillable = [
        'title',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'dimensions',
        'alt_text',
        'category',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Ensure media_assets table exists and seed initial images if table was empty.
     */
    public function ensureTableExists(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS media_assets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                file_size INT DEFAULT 0,
                mime_type VARCHAR(100) DEFAULT 'image/jpeg',
                dimensions VARCHAR(50) DEFAULT NULL,
                alt_text VARCHAR(255) DEFAULT NULL,
                category VARCHAR(50) DEFAULT 'general',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Sync images existing in assets/images/ if table is currently empty
            $count = (int) $this->db->query("SELECT COUNT(*) FROM media_assets")->fetchColumn();
            if ($count === 0) {
                $this->syncExistingImages();
            }
        } catch (Throwable $e) {
            // Ignore error if database user permissions restrict table inspection
        }
    }

    /**
     * Scan assets/images directory and insert any discovered image files into media_assets table.
     */
    public function syncExistingImages(): int
    {
        $baseDir = __DIR__ . '/../../assets/images/';
        if (!is_dir($baseDir)) {
            return 0;
        }

        $inserted = 0;
        $files = scandir($baseDir);
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($baseDir . $file)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                continue;
            }

            $filePath = 'assets/images/' . $file;
            $fullPath = $baseDir . $file;
            $fileSize = filesize($fullPath) ?: 0;

            // Check if already registered
            $checkStmt = $this->db->prepare("SELECT id FROM media_assets WHERE file_path = ? OR filename = ? LIMIT 1");
            $checkStmt->execute([$filePath, $file]);
            if ($checkStmt->fetch()) {
                continue;
            }

            $title = ucwords(str_replace(['-', '_'], ' ', pathinfo($file, PATHINFO_FILENAME)));
            $mimeType = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };

            $dimensions = null;
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $imageInfo = @getimagesize($fullPath);
                if ($imageInfo && isset($imageInfo[0], $imageInfo[1])) {
                    $dimensions = $imageInfo[0] . 'x' . $imageInfo[1];
                }
            }

            $category = 'general';
            if (str_contains($file, 'bromo') || str_contains($file, 'ijen') || str_contains($file, 'temple') || str_contains($file, 'waterfall') || str_contains($file, 'hills')) {
                $category = 'destination';
            } elseif (str_contains($file, 'blog')) {
                $category = 'blog';
            } elseif (str_contains($file, 'hero')) {
                $category = 'hero';
            }

            $stmt = $this->db->prepare(
                "INSERT INTO media_assets (title, filename, file_path, file_size, mime_type, dimensions, alt_text, category)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $title,
                $file,
                $filePath,
                $fileSize,
                $mimeType,
                $dimensions,
                $title,
                $category
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Get filtered list of assets with search, category filter, and pagination
     */
    public function getFilteredAssets(string $search = '', string $category = '', string $sort = 'newest'): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (title LIKE :search OR filename LIKE :search OR alt_text LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if ($category !== '' && $category !== 'all') {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }

        $orderBy = match ($sort) {
            'oldest' => 'created_at ASC',
            'size_desc' => 'file_size DESC',
            'name_asc' => 'title ASC',
            default => 'created_at DESC',
        };

        $sql .= " ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get summary statistics of media assets
     */
    public function getStats(): array
    {
        $totalCount = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        $totalBytes = (int) $this->db->query("SELECT COALESCE(SUM(file_size), 0) FROM {$this->table}")->fetchColumn();

        $categoryCounts = $this->db->query(
            "SELECT category, COUNT(*) as count FROM {$this->table} GROUP BY category"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'total_count' => $totalCount,
            'total_bytes' => $totalBytes,
            'categories'  => $categoryCounts,
        ];
    }
}
