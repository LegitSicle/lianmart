<?php
use core\web\Viewer;

function iterComponents(string $component, int $page, int $amount, ?array $data): void {
    if (empty($data)) return;
    $maxPageCount = (int) ceil(count($data) / $amount);
    $iter = $maxPageCount === $page ? count($data) - ($amount * ($maxPageCount - 1)) : $amount;
    for ($i = 0; $i < $iter; $i++) { 
        echo Viewer::getComponents($component, $data[(($page - 1) * $amount) + $i]);
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