<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;

class PingAction extends BaseAction
{
    public function requiresAuth(): bool
    {
        return false;
    }

    public function execute(array $params, ?array $user = null): array
    {
        return [
            'success' => true,
            'message' => 'pong',
            'version' => 'v1',
            'timestamp' => time()
        ];
    }
}
