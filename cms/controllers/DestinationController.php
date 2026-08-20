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

    /** GET /destinations */
    public function index(): void
    {
        $destinations = $this->model->withTourCount();
        $this->success($destinations, 'Destinations retrieved');
    }

    /** GET /destinations/{id} */
    public function show(int $id): void
    {
        $destination = $this->model->find($id);

        if (!$destination) {
            $this->error('Destination not found', 404);
        }

        $this->success($destination);
    }

    /** GET /destinations/slug/{slug} */
    public function showBySlug(string $slug): void
    {
        $destination = $this->model->findBySlug($slug);

        if (!$destination) {
            $this->error('Destination not found', 404);
        }

        $this->success($destination);
    }

    /** POST /destinations */
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
        $this->success(['id' => $id], 'Destination created', 201);
    }

    /** PUT/PATCH /destinations/{id} */
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
