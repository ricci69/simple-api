<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;

class ProfileAction extends BaseAction
{
    public function execute(array $params, ?array $user = null): array
    {
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ];
    }
}
