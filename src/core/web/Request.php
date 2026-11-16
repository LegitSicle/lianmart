<?php
namespace core\web;

use function strlen;
use function is_array;
use function in_array;
use function count;

/**
 * Main handler for super globals such as `$_SERVER`, `$_GET`, and `$_POST`.
 */
final class Request {
    /**
     * Used for routing, it returns the `$_SERVER["REQUEST_URI"]` that has the starting `'/website'` removed and anything proceeding '`?'`.
     * @return string The path route.
     */
    public function path(): string {
        $errorStatus = $this->apacheErrorStatus();
        if ($errorStatus !== null) return "/error/{$errorStatus}";

        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?? '/';
        $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        return $uri === '' ? '/' : $uri;
    }

    public function method(): string {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    public function isGet(): bool {
        return $this->method() === "get";
    }

    public function isPost(): bool {
        return $this->method() === "post";
    }

    public function apacheErrorStatus(): ?int {
        foreach ($_SERVER as $key => $value) {
            if (preg_match('/^(REDIRECT_)+STATUS$/', $key)) {
                $statusCode = (int) $value;
                if ($statusCode >= 400 && $statusCode < 600) {
                    return $statusCode;
                }
            }
        }
        return null;
    }

    /**
     * Gives the request body. For example for `GET`, `http://localhost/website/home?username=Sicle` link will have a body array of <br>
     * `["username" => "Sicle"]`.
     * The same for `POST`.
     * @return array A filtered array from `$_GET` or `$_POST`.
     */
    public function body(): array {
        $body = [];
        foreach ($_GET as $key => $value) {
            if (is_array($value)) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
            } else {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
            } else {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }    
        return $body;
    }

    public function uploads(array $allowedExtension = [], array $allowedMimeTypes = []): array {
        $uploads = [];

        foreach ($_FILES as $name => $file) {
            $isMultiple = is_array($file['name']);

            if ($isMultiple) {
                $validFiles = array_filter($file['name'], fn($fileName) => !empty($fileName));

                $fileCount = count($validFiles);

                if ($fileCount > 0) {
                    $allFilesGood = true;
                    $totalSlots = count($file['name']);

                    for ($i = 0; $i < $totalSlots; $i++) {
                        if (empty($file['name'][$i])) {
                            continue;
                        }

                        if (!$this->isValidUpload($file, $allowedExtension, $allowedMimeTypes, $i)) {
                            $allFilesGood = false;
                            break;
                        }
                    }

                    if ($allFilesGood) {
                        $uploads[$name] = $file;
                    }
                }
            } else {
                if (!empty($file['name'])) {
                    if ($this->isValidUpload($file, $allowedExtension, $allowedMimeTypes)) {
                        $uploads[$name] = $file;
                    }
                }
            }
        }

        return $uploads;
    }

    private function isValidUpload(array $file, array $allowedExtensions = [], array $allowedMimeTypes = [], ?int $idx = null): bool {
        $error = isset($idx) ? $file["error"][$idx] : $file["error"];
        $name = isset($idx) ? $file["name"][$idx] : $file["name"];
        $tmpName = isset($idx) ? $file["tmp_name"][$idx] : $file["tmp_name"];

        if ($error === UPLOAD_ERR_NO_FILE || $error !== UPLOAD_ERR_OK) return false;
        $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) return false;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        if (!in_array($mimeType, $allowedMimeTypes)) return false;
        return true;
    }
}