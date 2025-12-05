<?php
namespace Api;

class Database
{
    private static ?\PDO $pdo = null;

    public static function connect(): \PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../config/config.php';
            $dbPath = $config['db_path'];
            
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            self::$pdo = new \PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::initTables();
        }
        
        return self::$pdo;
    }

    private static function initTables(): void
    {
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT UNIQUE NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip TEXT NOT NULL,
                requests INTEGER DEFAULT 1,
                window_start DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        self::$pdo->exec("CREATE INDEX IF NOT EXISTS idx_rate_ip ON rate_limits(ip)");
        self::$pdo->exec("CREATE INDEX IF NOT EXISTS idx_token ON tokens(token)");
    }
}
