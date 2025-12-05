<?php
namespace Api;

class Api
{
    private Auth $auth;
    private RateLimit $rateLimit;
    private array $config;
    private string $version;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->rateLimit = new RateLimit();
        $this->config = require __DIR__ . '/../config/config.php';
        $this->setCorsHeaders();
    }

    private function setCorsHeaders(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        if (in_array('*', $this->config['allowed_origins']) || in_array($origin, $this->config['allowed_origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        }
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function handle(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(['error' => 'Only POST requests are allowed'], 405);
            }

            $ip = $this->getClientIp();
            if (!$this->rateLimit->check($ip)) {
                $this->sendResponse(['error' => 'Too many requests. Please try again later'], 429);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $action = $input['action'] ?? '';
            $this->version = $input['version'] ?? $this->config['api_version'];
            
            if (!in_array($this->version, $this->config['api_versions'])) {
                $this->sendResponse(['error' => "API version '{$this->version}' not supported"], 400);
            }

            if (empty($action)) {
                $this->sendResponse(['error' => 'Action not specified'], 400);
            }

            $response = $this->executeAction($action, $input);
            $this->sendResponse($response);

        } catch (\Exception $e) {
            $error = ['error' => 'Internal server error'];
            if ($this->config['debug']) {
                $error['details'] = $e->getMessage();
                $error['trace'] = $e->getTraceAsString();
            }
            $this->sendResponse($error, 500);
        }
    }

    private function executeAction(string $action, array $params): array
    {
        $actionClass = $this->getActionClass($action);
        
        if (!class_exists($actionClass)) {
            return ['error' => "Action '$action' not found in version {$this->version}"];
        }

        try {
            $actionInstance = new $actionClass();
            
            $user = null;
            if ($actionInstance->requiresAuth()) {
                $token = $this->getBearerToken();
                if (!$token) {
                    return ['error' => 'Authentication token required'];
                }
                
                $user = $this->auth->verifyToken($token);
                if (!$user) {
                    return ['error' => 'Invalid or expired token'];
                }
                
                $params['_token'] = $token;
            }

            return $actionInstance->execute($params, $user);
            
        } catch (\Exception $e) {
            if ($this->config['debug']) {
                return ['error' => $e->getMessage()];
            }
            return ['error' => 'Error executing the action'];
        }
    }

    private function getActionClass(string $action): string
    {
        $className = str_replace('_', '', ucwords($action, '_'));
        $versionNamespace = strtoupper($this->version);
        return "Api\\Actions\\{$versionNamespace}\\{$className}Action";
    }

    private function getBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function sendResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
