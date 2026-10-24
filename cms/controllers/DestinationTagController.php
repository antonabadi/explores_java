<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/DestinationTag.php';

class DestinationTagController extends Controller
{
    private DestinationTag $model;

    public function __construct()
    {
        $this->model = new DestinationTag();
    }

    /** GET /destination-tags */
    public function index(): void
    {
        $tags = $this->model->all('name', 'ASC');
        $this->success($tags, 'Destination tags retrieved');
    }

    /** GET /destination-tags/{id} */
    public function show(int $id): void
    {
        $tag = $this->model->find($id);

        if (!$tag) {
            $this->error('Destination tag not found', 404);
        }

        $this->success($tag);
    }

    /** POST /destination-tags */
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
        $this->success(['id' => $id], 'Destination tag created', 201);
    }

    /** PUT/PATCH /destination-tags/{id} */
    public function update(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination tag not found', 404);
        }

        $input = $this->getInput();

        if (!empty($input['name']) && $input['name'] !== $existing['name'] && empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['name'], $id);
        }

        $this->model->update($id, $input);
        $this->success(null, 'Destination tag updated');
    }

    /** DELETE /destination-tags/{id} */
    public function destroy(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination tag not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Destination tag deleted');
    }
}
