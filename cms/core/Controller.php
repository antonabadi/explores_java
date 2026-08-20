<?php

require_once __DIR__ . '/Auth.php';

abstract class Controller
{
    protected function requireAdmin(): void
    {
        Auth::requireAdmin();
    }

    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function success(mixed $data = null, string $message = 'OK', int $statusCode = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    protected function error(string $message = 'Error', int $statusCode = 400, mixed $errors = null): void
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    /** Reads JSON body (for POST/PUT sent as application/json) */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Merges $_POST with parsed JSON body, whichever is present */
    protected function getInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        return $this->getJsonInput();
    }

    protected function getQueryParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /** Validates required fields are present and non-empty; returns list of missing fields */
    protected function validateRequired(array $data, array $requiredFields): array
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
