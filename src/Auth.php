<?php
namespace Api;

class Auth
{
    private \PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function register(string $email, string $password, string $name): array
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['error' => 'Email already registered'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $name]);
        
        return [
            'success' => true,
            'user_id' => $this->db->lastInsertId(),
            'message' => 'User registered successfully'
        ];
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['error' => 'Invalid credentials'];
        }

        $token = $this->generateToken($user['id']);
        
        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ];
    }

    private function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->config['jwt_expiration']);
        
        $stmt = $this->db->prepare("INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, $expiresAt]);
        
        return $token;
    }

    public function verifyToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN tokens t ON u.id = t.user_id
            WHERE t.token = ? AND t.expires_at > datetime('now')
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function logout(string $token): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tokens WHERE token = ?");
        return $stmt->execute([$token]);
    }
}
