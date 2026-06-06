<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $params = [];
    
    public function add($method, $path, $handler) {
        $path = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[a-zA-Z0-9-]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => '#^' . $path . '$#',
            'handler' => $handler
        ];
    }
    
    public function get($path, $handler) {
        $this->add('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->add('POST', $path, $handler);
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                $this->callHandler($route['handler']);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function callHandler($handler) {
        list($controllerName, $method) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $this->params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function notFound() {
        http_response_code(404);
        echo "404 - Page Not Found";
        exit();
    }
}