<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;

class CartItems extends DatabaseModel {
    protected int $userId;
    protected int $productId;
    protected ?int $quantity = 1;

    #[Override]
    protected function rules(): array {
        return [];
    }
    
    #[Override]
    public function primaryKey(): string|array {
        return ["userId", "productId"];
    }

    #[Override]
    protected function attributes(): array {
        return ["userId", "productId", "quantity"];
    }
}