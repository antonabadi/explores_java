<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Destination.php';

class DestinationController extends Controller
{
    private Destination $model;

    public function __construct()
    {
        $this->model = new Destination();
    }

    /**
     * GET /destinations
     *
     * Query params opsional:
     *   ?area=banyuwangi
     *   ?tag=hiking,photography   (satu atau beberapa, pisah koma)
     *   ?keyword=kawah
     *   ?page=1&per_page=12
     */
    public function index(): void
    {
        $filters = [];

        if (!empty($_GET['area'])) {
            $filters['area'] = trim($_GET['area']);
        }
        if (!empty($_GET['tag'])) {
            $filters['tag'] = trim($_GET['tag']);
        }
        if (!empty($_GET['keyword'])) {
            $filters['keyword'] = trim($_GET['keyword']);
        }

        // Jika ada filter aktif, gunakan search(); jika tidak, pakai withTourCount()
        if (!empty($filters)) {
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = min(50, max(1, (int) ($_GET['per_page'] ?? 12)));
            $result  = $this->model->search($filters, $page, $perPage);
            $this->success($result, 'Destinations retrieved');
        } else {
            $destinations = $this->model->withTourCount();
            $this->success($destinations, 'Destinations retrieved');
        }
    }

    /**
     * GET /destinations/{id}
     * Menyertakan areas, tags, dan tour_count dalam response.
     */
    public function show(int $id): void
    {
        $destination = $this->model->withRelations($id);

        if (!$destination) {
            $this->error('Destination not found', 404);
        }

        $this->success($destination);
    }

    /**
     * GET /destinations/slug/{slug}
     * Menyertakan areas, tags, dan tour_count dalam response.
     */
    public function showBySlug(string $slug): void
    {
        $destination = $this->model->withRelationsBySlug($slug);

        if (!$destination) {
            $this->error('Destination not found', 404);
        }

        $this->success($destination);
    }

    /**
     * GET /destinations/{id}/blog-suggestions
     * Blog posts yang disarankan untuk destinasi ini (manual + auto fallback).
     */
    public function blogSuggestions(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination not found', 404);
        }

        $limit       = min(20, max(1, (int) ($_GET['limit'] ?? 6)));
        $suggestions = $this->model->getBlogSuggestions($id, $limit);
        $this->success($suggestions, 'Blog suggestions retrieved');
    }

    /**
     * POST /destinations
     *
     * Body JSON:
     * {
     *   "name": "Kawah Ijen",
     *   "description": "...",
     *   "image_thumbnail": "...",
     *   "location_map": "https://maps.google.com/...",
     *   "area_ids": [1, 2],
     *   "tag_ids": [3, 5, 7],
     *   "blog_posts": [{"post_id": 10, "sort_order": 0}, ...]
     * }
     */
    public function store(): void
    {
        $input = $this->getInput();

        $missing = $this->validateRequired($input, ['name']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if (empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['name']);
        }

        $id = $this->model->create($input);

        if (!empty($input['area_ids']) && is_array($input['area_ids'])) {
            $this->model->syncAreas($id, $input['area_ids']);
        }

        if (!empty($input['tag_ids']) && is_array($input['tag_ids'])) {
            $this->model->syncTags($id, $input['tag_ids']);
        }

        if (!empty($input['blog_posts']) && is_array($input['blog_posts'])) {
            $this->model->syncBlogPosts($id, $input['blog_posts']);
        }

        $this->success(['id' => $id], 'Destination created', 201);
    }

    /**
     * PUT/PATCH /destinations/{id}
     *
     * Kirim hanya field yang ingin diubah.
     * Untuk areas/tags/blog_posts: kirim array baru; pivot akan di-sync ulang.
     * Untuk tidak mengubah pivot, cukup tidak kirim key-nya.
     */
    public function update(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination not found', 404);
        }

        $input = $this->getInput();

        if (!empty($input['name']) && $input['name'] !== $existing['name'] && empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['name'], $id);
        }

        $this->model->update($id, $input);

        if (array_key_exists('area_ids', $input)) {
            $this->model->syncAreas($id, (array) $input['area_ids']);
        }

        if (array_key_exists('tag_ids', $input)) {
            $this->model->syncTags($id, (array) $input['tag_ids']);
        }

        if (array_key_exists('blog_posts', $input)) {
            $this->model->syncBlogPosts($id, (array) $input['blog_posts']);
        }

        $this->success(null, 'Destination updated');
    }

    /** DELETE /destinations/{id} */
    public function destroy(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Destination deleted');
    }
}
