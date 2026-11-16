<?php
namespace core\controller;
use core\web\Request;
use core\web\Response;

abstract class Middleware {
    private static array $registries = [];
    abstract public function handle(Request $request, callable $next): Response;

    public static function insertShared(string $key, mixed $value): void {
        $class = static::class;
        
        if (!isset(self::$registries[$class])) {
            self::$registries[$class] = [];
        }

        self::$registries[$class][$key] = $value;
    }

    public static function getShared(string $key): mixed {
        $class = static::class;
        return self::$registries[$class][$key] ?? null;
    }
}