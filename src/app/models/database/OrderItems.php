<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;

class OrderItems extends DatabaseModel {
    protected int $orderId;
    protected int $productId;
    protected int $quantity;
    protected float $purchasePrice;

    #[Override]
    protected function attributes(): array {
        return ["orderId", "productId", "quantity", "purchasePrice"];
    }

    #[Override]
    protected function rules(): array {
        return [];
    }

    #[Override]
    public function primaryKey(): string|array {
        return ["orderId", "productId"];
    }
}