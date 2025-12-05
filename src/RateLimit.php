<?php
namespace Api;

class RateLimit
{
    private \PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function check(string $ip): bool
    {
        $this->db->exec("DELETE FROM rate_limits WHERE datetime(window_start, '+1 hour') < datetime('now')");
        
        $stmt = $this->db->prepare("
            SELECT requests FROM rate_limits 
            WHERE ip = ? AND datetime(window_start, '+1 hour') > datetime('now')
        ");
        $stmt->execute([$ip]);
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($record) {
            if ($record['requests'] >= $this->config['rate_limit']) {
                return false;
            }
            
            $stmt = $this->db->prepare("UPDATE rate_limits SET requests = requests + 1 WHERE ip = ?");
            $stmt->execute([$ip]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO rate_limits (ip, requests) VALUES (?, 1)");
            $stmt->execute([$ip]);
        }
        
        return true;
    }
}
