<?php
namespace Api\Actions\V2;

use Api\Actions\BaseAction;

/**
 * V2 della lista utenti.
 * Oltre ai campi già restituiti nella V1 aggiunge:
 *   - updated_at  : data/ora dell'ultimo aggiornamento del record
 *   - last_login  : data/ora dell'ultimo accesso dell'utente (se presente)
 *   - is_active   : flag booleano che indica se l'utente è attivo
 */
class UsersListAction extends BaseAction
{
    public function execute(array $params, ?array $user = null): array
    {
        $page  = (int) ($params['page'] ?? 1);
        $limit = min((int) ($params['limit'] ?? 10), 100);
        $offset = ($page - 1) * $limit;

        // Query con i nuovi campi
        $stmt = $this->db->prepare("
            SELECT 
                id,
                email,
                name,
                role,
                created_at,
                updated_at,
                last_login,
                is_active
            FROM users
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        return [
            'success' => true,
            'users'   => $users,
            'pagination' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / $limit)
            ],
            // Parametri aggiuntivi di risposta
            'metadata' => [
                'generated_at' => date('c'),          // timestamp ISO 8601
                'api_version'  => 'v2'                // indica la versione dell'endpoint
            ]
        ];
    }
}