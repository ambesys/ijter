<?php
// core/Router.php

class Router {
    private $routes = [];
    private $groupMiddleware = [];

    // HTTP method handlers
    public function get($path, $handler, $middleware = null) {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = null) {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put($path, $handler, $middleware = null) {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete($path, $handler, $middleware = null) {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    // Group routes with middleware
    public function group(array $attributes, callable $callback) {
        $previousGroupMiddleware = $this->groupMiddleware;
        
        if (isset($attributes['middleware'])) {
            $this->groupMiddleware[] = $attributes['middleware'];
        }

        $callback($this);
        
        $this->groupMiddleware = $previousGroupMiddleware;
    }

    // Add route to collection
    private function addRoute($method, $path, $handler, $middleware = null) {
        $allMiddleware = $this->groupMiddleware;
        if ($middleware) {
            $allMiddleware[] = $middleware;
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $allMiddleware
        ];

        return $this;
    }

public function dispatch($method, $uri) {
    $uri = parse_url($uri, PHP_URL_PATH);
    // Keep the leading slash but remove trailing slash
    $uri = '/' . trim($uri, '/');

    foreach ($this->routes as $route) {
        if ($this->matchRoute($route, $method, $uri)) {
            // Execute middleware chain
            foreach ($route['middleware'] as $middleware) {
                $middleware();
            }
            
            // Execute route handler
            return call_user_func($route['handler']);
        }
    }
    
    // 404 handling
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
}


private function matchRoute($route, $method, $uri) {
    if ($route['method'] !== $method) {
        return false;
    }

    // Ensure route path starts with a slash
    $routePath = '/' . trim($route['path'], '/');
    
    // Convert route parameters to regex pattern
    $pattern = preg_replace('/\\/:([^\\/]+)/', '/(?P<$1>[^/]+)', $routePath);
    // Don't trim the pattern, keep the leading slash
    $pattern = "#^" . $pattern . "$#";

    if (preg_match($pattern, $uri, $matches)) {
        // Filter out numeric keys from matches
        $params = array_filter($matches, function($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        // Store route parameters in request
        $_GET = array_merge($_GET, $params);
        return true;
    }

    return false;
}


    // Add middleware to route
    public function middleware($middleware) {
        $lastRoute = end($this->routes);
        if ($lastRoute) {
            $lastRoute['middleware'][] = $middleware;
            $this->routes[key($this->routes)] = $lastRoute;
        }
        return $this;
    }
}
