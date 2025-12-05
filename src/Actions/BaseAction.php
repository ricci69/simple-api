<?php
namespace Api\Actions;

use Api\Database;

abstract class BaseAction implements ActionInterface
{
    protected \PDO $db;
    protected array $config;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    protected function validateRequired(array $params, array $required): ?array
    {
        $missing = [];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return ['error' => 'Missing fields: ' . implode(', ', $missing)];
        }
        
        return null;
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function requiresAuth(): bool
    {
        return true;
    }
}
