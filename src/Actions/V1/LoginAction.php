<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;
use Api\Auth;

class LoginAction extends BaseAction
{
    public function requiresAuth(): bool
    {
        return false;
    }

    public function execute(array $params, ?array $user = null): array
    {
        $validationError = $this->validateRequired($params, ['email', 'password']);
        if ($validationError) {
            return $validationError;
        }

        $auth = new Auth();
        return $auth->login($params['email'], $params['password']);
    }
}
