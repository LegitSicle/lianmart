<?php
namespace core\web;
use Exception;
use function call_user_func;

class Router {
    /**
     * @var array<callable(Request): Response>
     */
    protected array $routes;

    public function addRoute(Route $route): void {
        $this->routes[$route->method][$route->url] = function (Request $request) use ($route) {
            $controller = new $route->controllerClass($route->data ?? []);

            if (!isset($route->middlewares)) return $controller->index($request);
            $next = $controller->index(...);
            foreach (array_reverse($route->middlewares) as $middlewareArray) {
                $middlewareClass = array_shift($middlewareArray);
                $middleware = new $middlewareClass(...$middlewareArray);
                $next = fn(Request $request) => $middleware->handle($request, $next);
            }
            return $next($request);
        };
    }

    public function resolve(Request $request): Response {
        $path = $request->path();
        $method = $request->method();
        $route = $this->routes[$method][$path] ?? null;
        if (empty($route)) throw new Exception("Route Not Found!", 404);
        return call_user_func($route, $request);
    }
}