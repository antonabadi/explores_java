<?php

require_once __DIR__ . '/../core/Model.php';

class Booking extends Model
{
    protected string $table = 'bookings';

    protected array $fillable = [
        'tour_id',
        'booking_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'quantity',
        'total_price',
        'booking_date',
        'status',
        'notes',
    ];

    public const STATUSES = ['pending', 'confirmed', 'cancelled', 'completed'];

    public function findByCode(string $bookingCode): array|false
    {
        return $this->findBy('booking_code', $bookingCode);
    }

    public function findWithTour(int $id): array|false
    {
        $sql = "SELECT b.*, t.title AS tour_title, t.slug AS tour_slug, t.price AS tour_price
                FROM {$this->table} b
                JOIN tours t ON t.id = b.tour_id
                WHERE b.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function byStatus(string $status): array
    {
        return $this->where('status', $status, 'created_at DESC');
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }
        return $this->update($id, ['status' => $status]);
    }

    public function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while ($this->findByCode($code) !== false);

        return $code;
    }

    /** Create a booking, auto-generating the code and computing total_price from the tour price */
    public function createBooking(array $data, float $tourPrice): int
    {
        $data['booking_code'] = $this->generateBookingCode();
        $data['total_price'] = $tourPrice * (int) $data['quantity'];
        $data['status'] = $data['status'] ?? 'pending';

        return $this->create($data);
    }
}
