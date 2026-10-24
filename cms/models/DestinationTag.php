<?php

require_once __DIR__ . '/../core/Model.php';

class DestinationTag extends Model
{
    protected string $table = 'destination_tags';

    protected array $fillable = [
        'name',
        'slug',
        'icon',
    ];

    public function findBySlug(string $slug): array|false
    {
        return $this->findBy('slug', $slug);
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
