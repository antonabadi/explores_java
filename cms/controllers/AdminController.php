<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Admin.php';

class AdminController extends Controller
{
    private Admin $model;

    public function __construct()
    {
        $this->model = new Admin();
    }

    /** GET /admins */
    public function index(): void
    {
        $admins = $this->model->all('fullname', 'ASC');
        // never leak password hashes
        foreach ($admins as &$a) {
            unset($a['password']);
        }
        $this->success($admins);
    }

    /** GET /admins/{id} */
    public function show(int $id): void
    {
        $admin = $this->model->find($id);

        if (!$admin) {
            $this->error('Admin not found', 404);
        }

        unset($admin['password']);
        $this->success($admin);
    }

    /** POST /admins */
    public function store(): void
    {
        $input = $this->getInput();

        $required = ['username', 'email', 'password', 'fullname'];
        $missing = $this->validateRequired($input, $required);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address', 422);
        }

        if (strlen($input['password']) < 8) {
            $this->error('Password must be at least 8 characters', 422);
        }

        if ($this->model->findByUsername($input['username'])) {
            $this->error('Username already taken', 422);
        }

        if (isset($input['role'])) {
            if (!$this->model->isValidRole($input['role'])) {
                $this->error('Invalid role', 422);
            }
        } else {
            $input['role'] = 'admin-content';
        }

        if ($this->model->findByEmail($input['email'])) {
            $this->error('Email already registered', 422);
        }

        $id = $this->model->create($input);
        $this->success(['id' => $id], 'Admin created', 201);
    }

    /** PUT/PATCH /admins/{id} */
    public function update(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Admin not found', 404);
        }

        $input = $this->getInput();

        if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address', 422);
        }

        if (isset($input['password']) && $input['password'] !== '' && strlen($input['password']) < 8) {
            $this->error('Password must be at least 8 characters', 422);
        }

        if (isset($input['role']) && !$this->model->isValidRole($input['role'])) {
            $this->error('Invalid role', 422);
        }

        $this->model->update($id, $input);
        $this->success(null, 'Admin updated');
    }

    /** DELETE /admins/{id} */
    public function destroy(int $id): void
    {
        if (!$this->model->find($id)) {
            $this->error('Admin not found', 404);
        }

        $this->model->delete($id);
        $this->success(null, 'Admin deleted');
    }

    /** POST /admins/login */
    public function login(): void
    {
        $input = $this->getInput();

        $missing = $this->validateRequired($input, ['username', 'password']);
        if ($missing) {
            $this->error('Missing required fields', 422, $missing);
        }

        $admin = $this->model->verifyPassword($input['username'], $input['password']);

        if (!$admin) {
            $this->error('Invalid credentials', 401);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'] ?? 'admin-content';

        $this->success($admin, 'Login successful');
    }

    /** POST /admins/logout */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();

        $this->success(null, 'Logged out');
    }
}
