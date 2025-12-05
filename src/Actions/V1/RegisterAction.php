<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;
use Api\Auth;

class RegisterAction extends BaseAction
{
    public function requiresAuth(): bool
    {
        return false;
    }

    public function execute(array $params, ?array $user = null): array
    {
        $validationError = $this->validateRequired($params, ['email', 'password', 'name']);
        if ($validationError) {
            return $validationError;
        }

        if (!$this->validateEmail($params['email'])) {
            return ['error' => 'Invalid email'];
        }

        if (strlen($params['password']) < 6) {
            return ['error' => 'Password must be at least 6 characters long'];
        }

        $auth = new Auth();
        return $auth->register($params['email'], $params['password'], $params['name']);
    }
}
