<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;
use Api\Auth;

class LogoutAction extends BaseAction
{
    public function execute(array $params, ?array $user = null): array
    {
        $token = $params['_token'] ?? null;
        
        if ($token) {
            $auth = new Auth();
            $auth->logout($token);
        }

        return [
            'success' => true,
            'message' => 'Successfully logged out'
        ];
    }
}
