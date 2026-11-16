<?php
namespace {
    function cleanPath(string $path): string {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}