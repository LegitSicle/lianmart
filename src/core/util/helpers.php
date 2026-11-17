<?php
use core\web\Viewer;

function iterComponents(string $component, int $page, int $amount, ?array $data): void {
    if (empty($data) || $page < 1 || $amount < 1) return;

    $offset = ($page - 1) * $amount;
    $pageItems = array_slice($data, $offset, $amount);

    foreach ($pageItems as $item) {
        if (is_array($item)) {
            echo Viewer::getComponents($component, $item);
        }
    }
}

function safe_encode(mixed $value, bool $doubleEncode = true): string {
    if ($value instanceof BackedEnum) {
        $value = $value->value;
    }
    if ($value === null) {
        return '';
    }
    if (!is_scalar($value) && !$value instanceof Stringable) {
        trigger_error('safe_encode() cannot convert ' . get_debug_type($value) . ' to a string.', E_USER_WARNING);
        return '';
    }
 
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        'UTF-8',
        $doubleEncode
    );
}
 
function safe_url(mixed $value, array $schemes = ['http', 'https', 'mailto', 'tel']): string {
    $url = trim((string) ($value ?? ''));
 
    $compact = preg_replace('/[\x00-\x20\x7F]+/', '', $url) ?? '';
 
    if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $match)
        && !in_array(strtolower($match[1]), $schemes, true)) {
        return '';
    }
 
    return safe_encode($url);
}