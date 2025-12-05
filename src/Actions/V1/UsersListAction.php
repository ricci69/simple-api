<?php
namespace Api\Actions\V1;

use Api\Actions\BaseAction;

class UsersListAction extends BaseAction
{
    public function execute(array $params, ?array $user = null): array
    {
        $page = (int) ($params['page'] ?? 1);
        $limit = min((int) ($params['limit'] ?? 10), 100);
        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT id, email, name, role, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        return [
            'success' => true,
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
}
