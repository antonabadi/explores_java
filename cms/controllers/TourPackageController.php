<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TourPackage.php';

class TourPackageController extends Controller
{
    private TourPackage $model;

    public function __construct()
    {
        $this->model = new TourPackage();
    }

    /** GET /packages */
    public function index(): void
    {
        $this->success($this->model->all('package_name', 'ASC'));
    }

    /** GET /packages/{id} */
    public function show(int $id): void
    {
        $package = $this->model->find($id);

        if (!$package) {
            $this->error('Package not found', 404);
        }

        $this->success($package);
    }

    /** POST /packages */
    public function store(): void
    {
        $input = $this->getInput();

        $missing = $this->validateRequired($input, ['package_name']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if ($this->model->findByName($input['package_name'])) {
            $this->error('Package name already exists', 422);
        }

        $id = $this->model->create($input);
        $this->success(['id' => $id], 'Package created', 201);
    }

    /** PUT/PATCH /packages/{id} */
    public function update(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Package not found', 404);
        }

        $input = $this->getInput();
        $this->model->update($id, $input);
        $this->success(null, 'Package updated');
    }

    /** DELETE /packages/{id} */
    public function destroy(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Package not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Package deleted');
    }
}
