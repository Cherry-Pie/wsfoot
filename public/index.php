<?php 
error_reporting(E_ALL);
ini_set('display_errors', 0);

set_error_handler(function($type, $message, $file, $line) {
    $e = new \ErrorException($message, $type, 1, $file, $line);
    handle_exception($e);
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error) {
        $e = new \ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
        handle_exception($e);
    }
});

session_start();

require '../app/helpers.php';


function handle_exception($e) {
    $template = App\Config::get('debug') ? 'debug' : '5xx';
    $view = new App\Template($template, compact('e'));

    $response = new Symfony\Component\HttpFoundation\Response(
        $view->fetch(), 
        Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR
    );
    
    $response->send();
} // end handle_exception

require __DIR__ . '/../vendor/autoload.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

try {
    $routes = require app_path('app/Http/routes.php');

    $view = new App\Template('404');
    $response = new Response($view->fetch(), Response::HTTP_NOT_FOUND);

    $request = Request::createFromGlobals();

    $uri = $request->getPathInfo();

    foreach ($routes as $route => $destination) {
        if ($uri != $route) {
            continue;
        }

        list($controllerName, $method) = explode('@', $destination);

        $controller = new $controllerName($request);
        $controller->init();
        $response = $controller->$method();
    }

    $response->send();
} catch (\Exception $e) {
    handle_exception($e);
}
