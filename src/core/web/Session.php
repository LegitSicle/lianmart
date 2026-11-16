<?php
namespace core\web;

class Session {
    protected const FLASH_KEY = "flash_values";
    protected const TIMED_KEY = "timed_values";

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flashValues = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashValues as $key => &$flashValue) {
            $flashValue["remove"] = true;
        }

        $_SESSION[self::FLASH_KEY] = $flashValues;
    }

    public function __destruct() {
        $flashValues = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashValues as $key => &$flashValue) {
            if ($flashValue["remove"]) {
                unset($flashValues[$key]);
            }
        }

        $_SESSION[self::FLASH_KEY] = $flashValues;

        $timedValues = $_SESSION[self::TIMED_KEY] ?? [];
        foreach ($timedValues as $key => &$timedValue) {
            if (time() > $timedValue["expires"]) {
                unset($timedValues[$key]);
            }
        }
        $_SESSION[self::TIMED_KEY] = $timedValues;
    }

    public function setFlash(string $key, mixed $value): void {
        $_SESSION[self::FLASH_KEY][$key] = [
            "remove" => false,
            "value" => $value,
        ];
    }


    public function getFlash(string $key): mixed {
        return $_SESSION[self::FLASH_KEY][$key]["value"] ?? false;
    }

    public function setTimed(string $key, mixed $value, int $seconds): void {
        $_SESSION[self::TIMED_KEY][$key] = [
            "expires" => time() + $seconds,
            "value" => $value
        ];
    }

    public function getTimed(string $key): mixed {
        if (empty($_SESSION[self::TIMED_KEY][$key])) return null;
        if (time() > $_SESSION[self::TIMED_KEY][$key]["expires"]) {
            unset($_SESSION[self::TIMED_KEY][$key]);
            return null;
        }
        return $_SESSION[self::TIMED_KEY][$key]["value"];
    }

    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed {
        return $_SESSION[$key] ?? false;
    }

    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public function removeTimed(string $key): void {
        unset($_SESSION[self::TIMED_KEY][$key]);
    }

    public function isPresent(string $key): bool {
        return isset($_SESSION[$key]);
    }
}