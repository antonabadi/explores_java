<?php

require_once __DIR__ . '/../core/Model.php';

class Admin extends Model
{
    protected string $table = 'admins';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'fullname',
    ];

    public function findByUsername(string $username): array|false
    {
        return $this->findBy('username', $username);
    }

    public function findByEmail(string $email): array|false
    {
        return $this->findBy('email', $email);
    }

    public function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return parent::update($id, $data);
    }

    public function verifyPassword(string $usernameOrEmail, string $password): array|false
    {
        $admin = $this->findByUsername($usernameOrEmail) ?: $this->findByEmail($usernameOrEmail);

        if ($admin && password_verify($password, $admin['password'])) {
            unset($admin['password']);
            return $admin;
        }

        return false;
    }
}
