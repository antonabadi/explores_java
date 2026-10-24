<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/DestinationArea.php';

class DestinationAreaController extends Controller
{
    private DestinationArea $model;

    public function __construct()
    {
        $this->model = new DestinationArea();
    }

    /** GET /destination-areas */
    public function index(): void
    {
        $areas = $this->model->all('name', 'ASC');
        $this->success($areas, 'Destination areas retrieved');
    }

    /** GET /destination-areas/{id} */
    public function show(int $id): void
    {
        $area = $this->model->find($id);

        if (!$area) {
            $this->error('Destination area not found', 404);
        }

        $this->success($area);
    }

    /** POST /destination-areas */
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
        $this->success(['id' => $id], 'Destination area created', 201);
    }

    /** PUT/PATCH /destination-areas/{id} */
    public function update(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination area not found', 404);
        }

        $input = $this->getInput();

        if (!empty($input['name']) && $input['name'] !== $existing['name'] && empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['name'], $id);
        }

        $this->model->update($id, $input);
        $this->success(null, 'Destination area updated');
    }

    /** DELETE /destination-areas/{id} */
    public function destroy(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Destination area not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Destination area deleted');
    }
}
