<?php
// Disable all reporting errors
// error_reporting(0);
spl_autoload_register(function ($class_name) {
    require_once dirname(__DIR__) . '/classes/' .$class_name . '.php';
});

// Supported HTTP methods
$methods = ['GET', 'POST', 'PUT'];

$requestMethod = !empty($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], $methods) ?
                $_SERVER['REQUEST_METHOD'] : null;

// Respond with 404 for not supported methods
if (is_null($requestMethod)) {
    $response = new Response();
    $response->setHeaders([$_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found']);
} else {
    /**
     * Use the assigned class for handling the request
     * GetAction => GET
     * PostAction => POST
     * PutAction => PUT
     */
    $className = (ucfirst(strtolower($requestMethod))) . 'Action';
    $action = new $className;
    $response = $action->run();
}

$response->finish();