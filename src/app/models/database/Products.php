<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;

class Products extends DatabaseModel {
    protected ?int $productId = null;
    protected string $productName;
    protected int $categoryId;
    protected float $productPrice;
    protected string $productDetails = "";
    protected int $stock;

    #[Override]
    protected function attributes(): array {
        return ["productName", "categoryId", "productPrice", "productDetails", "stock"];
    }

    #[Override]
    public function primaryKey(): string {
        return "productId";
    }

    #[Override]
    protected function rules(): array {
        $rules = [
            "productName" => [self::RULE_REQUIRED, self::RULE_UNIQUE, self::RULE_MAX => 255],
            "categoryId" => [self::RULE_REQUIRED],
            "productPrice" => [self::RULE_REQUIRED],
        ];
        if (isset($this->productId)) {
            $rules["productName"] = [self::RULE_REQUIRED, self::RULE_UNIQUE => [$this->primaryKey() => $this->productId], self::RULE_MAX => 255];
        }
        return $rules;
    }

    #[Override]
    protected function errorMessage(): array {
        $errors = parent::errorMessage();
        $errors[self::RULE_UNIQUE] = "There is already another product with the same name!";
        return $errors;
    }
}