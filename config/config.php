<?php

return [
    'db_path' => __DIR__ . '/../data/api.db',
    'jwt_secret' => 'change-this-secret-key-in-production',
    'jwt_expiration' => 3600,
    'rate_limit' => 100,
    'allowed_origins' => ['*'],
    'api_version' => 'v1',
    'api_versions' => ['v1', 'v2'],
    'debug' => true
];
