<?php
namespace core\web;
use function is_array;
use function is_string;

final class AJAXRequest {
    private function __construct() {
        header("Content-Type: application/json");
    }

    public function getJson(): array {
        $rawInput = file_get_contents("php://input");
        $unfilteredData = json_decode($rawInput, true);
        return $this->sanitize_incoming_data($unfilteredData);
    }

    private function sanitize_incoming_data(array $data): array {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $cleanKey = strip_tags((string) $key);
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitize_incoming_data($value);
            } else {
                if (is_string($value)) {
                    $value = trim($value);
                    $value = strip_tags($value);
                } elseif (is_numeric($value)) {
                    $value = filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? 
                            (strpos((string)$value, '.') !== false ? (float)$value : (int)$value) : 
                            $value;
                }
                $sanitized[$cleanKey] = $value;
            }
        }

        return $sanitized;
    }

    public static function receive(): self {
        return new AJAXRequest();
    }
}