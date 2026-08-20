<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tour.php';

class TourController extends Controller
{
    private Tour $model;

    public function __construct()
    {
        $this->model = new Tour();
    }

    /** GET /tours  -- filtered, paginated search over active tours */
    public function index(): void
    {
        $filters = [
            'destination_id' => $this->getQueryParam('destination_id'),
            'package_id'      => $this->getQueryParam('package_id'),
            'group_type'      => $this->getQueryParam('group_type'),
            'min_price'       => $this->getQueryParam('min_price'),
            'max_price'       => $this->getQueryParam('max_price'),
            'min_duration'    => $this->getQueryParam('min_duration'),
            'max_duration'    => $this->getQueryParam('max_duration'),
            'keyword'         => $this->getQueryParam('keyword'),
        ];

        $page = (int) $this->getQueryParam('page', 1);
        $perPage = (int) $this->getQueryParam('per_page', 12);

        $result = $this->model->search(array_filter($filters, fn($v) => $v !== null && $v !== ''), $page, $perPage);
        $this->success($result, 'Tours retrieved');
    }

    /** GET /tours/{id} */
    public function show(int $id): void
    {
        $tour = $this->model->findWithRelations($id);

        if (!$tour) {
            $this->error('Tour not found', 404);
        }

        $this->success($tour);
    }

    /** GET /tours/slug/{slug} */
    public function showBySlug(string $slug): void
    {
        $tour = $this->model->findBySlugWithRelations($slug);

        if (!$tour) {
            $this->error('Tour not found', 404);
        }

        $this->success($tour);
    }

    /** POST /tours */
    public function store(): void
    {
        $input = $this->getInput();

        $required = [
            'destination_id', 'package_id', 'title', 'duration_days',
            'duration_nights', 'price', 'group_type', 'description', 'itinerary',
        ];
        $missing = $this->validateRequired($input, $required);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if (!in_array($input['group_type'], ['private', 'join_group'], true)) {
            $this->error('Invalid group_type', 422);
        }

        if (empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['title']);
        }

        $id = $this->model->create($input);

        // Optional: accept multiple image paths on creation
        if (!empty($input['images']) && is_array($input['images'])) {
            foreach ($input['images'] as $imagePath) {
                $this->model->addImage($id, $imagePath);
            }
        }

        $this->success(['id' => $id], 'Tour created', 201);
    }

    /** PUT/PATCH /tours/{id} */
    public function update(int $id): void
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            $this->error('Tour not found', 404);
        }

        $input = $this->getInput();

        if (isset($input['group_type']) && !in_array($input['group_type'], ['private', 'join_group'], true)) {
            $this->error('Invalid group_type', 422);
        }

        if (!empty($input['title']) && $input['title'] !== $existing['title'] && empty($input['slug'])) {
            $input['slug'] = $this->model->generateUniqueSlug($input['title'], $id);
        }

        $this->model->update($id, $input);
        $this->success(null, 'Tour updated');
    }

    /** DELETE /tours/{id} */
    public function destroy(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Tour not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Tour deleted');
    }

    /** POST /tours/{id}/images */
    public function addImage(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Tour not found', 404);
        }

        $input = $this->getInput();
        $missing = $this->validateRequired($input, ['image_path']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        $imageId = $this->model->addImage($id, $input['image_path']);
        $this->success(['id' => $imageId], 'Image added', 201);
    }

    /** DELETE /tours/images/{imageId} */
    public function deleteImage(int $imageId): void
    {
        $this->model->deleteImage($imageId);
        $this->success(null, 'Image deleted');
    }
}
