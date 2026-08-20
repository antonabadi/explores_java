<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Testimonial.php';
require_once __DIR__ . '/../models/Tour.php';

class TestimonialController extends Controller
{
    private Testimonial $model;
    private Tour $tourModel;

    public function __construct()
    {
        $this->model = new Testimonial();
        $this->tourModel = new Tour();
    }

    /** GET /testimonials  (?tour_id=, ?pending=1) */
    public function index(): void
    {
        $tourId = $this->getQueryParam('tour_id');
        $pending = $this->getQueryParam('pending');

        if ($pending) {
            $this->success($this->model->pending(), 'Pending testimonials retrieved');
            return;
        }

        if ($tourId) {
            $this->success($this->model->forTour((int) $tourId), 'Testimonials retrieved');
            return;
        }

        $this->success($this->model->approved(), 'Approved testimonials retrieved');
    }

    /** GET /testimonials/{id} */
    public function show(int $id): void
    {
        $testimonial = $this->model->find($id);

        if (!$testimonial) {
            $this->error('Testimonial not found', 404);
        }

        $this->success($testimonial);
    }

    /** POST /testimonials */
    public function store(): void
    {
        $input = $this->getInput();

        $required = ['customer_name', 'rating', 'review_text'];
        $missing = $this->validateRequired($input, $required);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        $rating = (int) $input['rating'];
        if ($rating < 1 || $rating > 5) {
            $this->error('Rating must be between 1 and 5', 422);
        }

        if (!empty($input['tour_id']) && !$this->tourModel->find((int) $input['tour_id'])) {
            $this->error('Tour not found', 404);
        }

        // Public submissions default to unapproved pending moderation
        $input['is_approved'] = 0;

        $id = $this->model->create($input);
        $this->success(['id' => $id], 'Testimonial submitted, pending approval', 201);
    }

    /** PATCH /testimonials/{id}/approve */
    public function approve(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Testimonial not found', 404);
        }

        $this->model->approve($id);
        $this->success(null, 'Testimonial approved');
    }

    /** PATCH /testimonials/{id}/reject */
    public function reject(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Testimonial not found', 404);
        }

        $this->model->reject($id);
        $this->success(null, 'Testimonial rejected');
    }

    /** PUT/PATCH /testimonials/{id} */
    public function update(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Testimonial not found', 404);
        }

        $input = $this->getInput();

        if (isset($input['rating'])) {
            $rating = (int) $input['rating'];
            if ($rating < 1 || $rating > 5) {
                $this->error('Rating must be between 1 and 5', 422);
            }
        }

        $this->model->update($id, $input);
        $this->success(null, 'Testimonial updated');
    }

    /** DELETE /testimonials/{id} */
    public function destroy(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Testimonial not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Testimonial deleted');
    }
}
