<?php
namespace core\web;
use Exception;

class Viewer {
    private static string $viewPath;
    private static string $layoutPath;
    private static string $componentPath;

    public static function viewExists(string $name): bool {
        return file_exists(static::$viewPath . "{$name}.php");
    }

    public static function getContents(string $view, string $layout, array $params = []): string {
        return str_replace("{{content}}", static::getView($view, $params), static::getLayout($layout, $params));
    }

    public static function getView(string $name, array $params = []): string {
        return static::getRender(static::getFilePath(static::$viewPath, $name), $params);
    }

    public static function getLayout(string $name, array $params = []): string {
        return static::getRender(static::getFilePath(static::$layoutPath, $name), $params);
    }

    public static function getComponents(string $name, array $params = []): string {
        return static::getRender(static::getFilePath(static::$componentPath, $name), $params);
    }

    public static function setPaths(string $viewPath, ?string $layoutPath = null, ?string $componentPath = null): void {
        static::$viewPath = $viewPath;
        static::$layoutPath = $layoutPath ?? "{$viewPath}layouts" . DIRECTORY_SEPARATOR;
        static::$componentPath = $componentPath ?? "{$viewPath}components" . DIRECTORY_SEPARATOR;
    }

    private static function getRender(string $filePath, array $params = []): string {
        if (!file_exists($filePath)) throw new Exception(basename($filePath) . " does not exists", 404);
        if (!empty($params)) extract($params);
        ob_start();
        require $filePath;
        return ob_get_clean();
    }

    private static function getFilePath(string $path, string $url): string {
        $file = ltrim($url, '/') . ".php";
        $filePath = $path . $file;
        if (file_exists($filePath)) return $filePath;
        $filePath = $path . str_replace("/", "-", $file);
        if (file_exists($filePath)) return $filePath;
        $filePath = $path . str_replace("/", "_", $file);
        return $filePath;
    }
}