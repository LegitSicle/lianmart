<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;

class Orders extends DatabaseModel {
    protected ?int $orderId = null;
    protected int $addressId;
    protected int $userId;
    protected float $total;
    protected string $orderStatus = "Processing";

    #[Override]
    protected function attributes(): array {
        return ["addressId", "userId", "total", "orderStatus"];
    }

    #[Override]
    protected function rules(): array {
        return [];
    }

    #[Override]
    public function primaryKey(): string|array {
        return "orderId";
    }
}