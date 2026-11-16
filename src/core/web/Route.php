<?php
namespace core\web;
use core\controller\Controller;
use Exception;
use core\controller\RedirectController;
use function is_string;

class Route {
    public readonly string $method;
    public readonly string $url;
    public readonly string $destination;
    public readonly string $controllerClass;
    public readonly array $data;
    public readonly array $middlewares;

    public function get(string $url, array $controller): self {
        $controllerClass = array_shift($controller);
        if (!is_subclass_of($controllerClass, Controller::class) || !is_string($controllerClass)) throw new Exception("{$controllerClass} is not a valid Controller", 500);
        $this->method = "get";
        $this->url = $url;
        $this->controllerClass = $controllerClass;
        if (!empty($controller)) $this->data = $controller;
        return $this;
    }

    public function post(string $url, array $controller): self {
        $controllerClass = array_shift($controller);
        if (!is_subclass_of($controllerClass, Controller::class) || !is_string($controllerClass)) throw new Exception("{$controllerClass} is not a valid Controller", 500);
        $this->method = "post";
        $this->url = $url;
        $this->controllerClass = $controllerClass;
        if (!empty($controller)) $this->data = $controller;
        return $this;
    }

    public function redirect(string $url, string $destination): self {
        $this->method = "get";
        $this->url = $url;
        $this->controllerClass = RedirectController::class;
        $this->data = ["destination" => $destination];
        return $this;
    }

    public function middlewares(array ...$middlewares): self {
        $this->middlewares = $middlewares;
        return $this;
    }
}