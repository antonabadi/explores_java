<?php

require_once __DIR__ . '/../core/Model.php';

class TourPackage extends Model
{
    protected string $table = 'tour_packages';

    protected array $fillable = [
        'package_name',
        'description',
    ];

    public function findByName(string $name): array|false
    {
        return $this->findBy('package_name', $name);
    }
}
