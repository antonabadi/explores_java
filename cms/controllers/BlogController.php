<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BlogPost.php';

class BlogController extends Controller
{
    private BlogPost $model;

    public function __construct()
    {
        $this->model = new BlogPost();
    }

    /** GET /blogs */
    public function index(): void
    {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $posts = $this->model->getPublished($limit);
        $this->success($posts, 'Blog posts retrieved');
    }

    /** GET /blogs/{id} */
    public function show(int $id): void
    {
        $post = $this->model->find($id);

        if (!$post) {
            $this->error('Blog post not found', 404);
        }

        $this->success($post);
    }

    /** GET /blogs/slug/{slug} */
    public function showBySlug(string $slug): void
    {
        $post = $this->model->findBySlug($slug);

        if (!$post) {
            $this->error('Blog post not found', 404);
        }

        $this->success($post);
    }

    /** POST /blogs */
    public function store(): void
    {
        $input = $this->getInput();

        $missing = $this->validateRequired($input, ['title', 'content']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if (empty($input['slug'])) {
            $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title']), '-'));
            $input['slug'] = $baseSlug ?: 'blog-post';
        }

        $id = $this->model->create($input);
        $this->success(['id' => $id], 'Blog post created', 201);
    }

    /** PUT/PATCH /blogs/{id} */
    public function update(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Blog post not found', 404);
        }

        $input = $this->getInput();
        $this->model->update($id, $input);
        $this->success(null, 'Blog post updated');
    }

    /** DELETE /blogs/{id} */
    public function destroy(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Blog post not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Blog post deleted');
    }
}
