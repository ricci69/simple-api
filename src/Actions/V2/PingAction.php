<?php
namespace Api\Actions\V2;

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
            'message' => 'pong from v2!',
            'version' => 'v2',
            'timestamp' => time(),
            'features' => [
                'enhanced_security' => true,
                'better_performance' => true
            ]
        ];
    }
}
