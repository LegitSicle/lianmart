<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;
use function is_string;

class ProductImages extends DatabaseModel {
    private const URL_PREFIX = "/lianmart/uploads/products";
    protected int $productId;
    protected string $imagePath;

    #[Override]
    public function update(): void {
        $old = self::findOne(["productId", "=", $this->productId]);
        if ($old) $old->deleteImage();
        parent::update();
    }

    #[Override]
    public function delete(): void {
        $this->deleteImage();
        parent::delete();
    }

    #[Override]
    protected function rules(): array {
        return [];
    }

    #[Override]
    public function primaryKey(): string {
        return "productId";
    }

    #[Override]
    protected function attributes(): array {
        return ["productId", "imagePath"];
    }

    private function deleteImage(): void {
        if (!$this->imagePath) return;

        $uploadDir = realpath(ROOT . "/public/uploads/products");
        if ($uploadDir === false) return;

        $urlPath = parse_url($this->imagePath, PHP_URL_PATH);
        if (!is_string($urlPath) || !str_starts_with($urlPath, self::URL_PREFIX)) return;

        $path = realpath($uploadDir . DIRECTORY_SEPARATOR . basename($urlPath));
        if ($path === false || !is_file($path) || !str_starts_with($path, $uploadDir . DIRECTORY_SEPARATOR)) return;

        if (!unlink($path)) {
            error_log("Failed to delete image: $path");
        }
    }
}