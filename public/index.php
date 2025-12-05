<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Api\Api;

$api = new Api();
$api->handle();
