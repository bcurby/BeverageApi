<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../includes/DbOperations.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);
