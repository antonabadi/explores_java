<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tour.php';

class BookingController extends Controller
{
    private Booking $model;
    private Tour $tourModel;

    public function __construct()
    {
        $this->model = new Booking();
        $this->tourModel = new Tour();
    }

    /** GET /bookings  (optionally ?status=pending) */
    public function index(): void
    {
        $status = $this->getQueryParam('status');

        if ($status) {
            if (!in_array($status, Booking::STATUSES, true)) {
                $this->error('Invalid status filter', 422);
            }
            $bookings = $this->model->byStatus($status);
        } else {
            $bookings = $this->model->all('created_at', 'DESC');
        }

        $this->success($bookings, 'Bookings retrieved');
    }

    /** GET /bookings/{id} */
    public function show(int $id): void
    {
        $booking = $this->model->findWithTour($id);

        if (!$booking) {
            $this->error('Booking not found', 404);
        }

        $this->success($booking);
    }

    /** GET /bookings/code/{code} */
    public function showByCode(string $code): void
    {
        $booking = $this->model->findByCode($code);

        if (!$booking) {
            $this->error('Booking not found', 404);
        }

        $this->success($booking);
    }

    /** POST /bookings */
    public function store(): void
    {
        $input = $this->getInput();

        $required = ['tour_id', 'customer_name', 'customer_email', 'customer_phone', 'quantity', 'booking_date'];
        $missing = $this->validateRequired($input, $required);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if (!filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address', 422);
        }

        if ((int) $input['quantity'] < 1) {
            $this->error('Quantity must be at least 1', 422);
        }

        $tour = $this->tourModel->find((int) $input['tour_id']);
        if (!$tour) {
            $this->error('Tour not found', 404);
        }
        if ((int) $tour['is_active'] !== 1) {
            $this->error('This tour is not currently available', 422);
        }

        $id = $this->model->createBooking($input, (float) $tour['price']);
        $booking = $this->model->find($id);

        $this->success($booking, 'Booking created', 201);
    }

    /** PATCH /bookings/{id}/status */
    public function updateStatus(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Booking not found', 404);
        }

        $input = $this->getInput();
        $missing = $this->validateRequired($input, ['status']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        try {
            $this->model->updateStatus($id, $input['status']);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        }

        $this->success(null, 'Booking status updated');
    }

    /** PUT/PATCH /bookings/{id} */
    public function update(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Booking not found', 404);
        }

        $input = $this->getInput();
        $this->model->update($id, $input);
        $this->success(null, 'Booking updated');
    }

    /** DELETE /bookings/{id} */
    public function destroy(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Booking not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Booking deleted');
    }
}
