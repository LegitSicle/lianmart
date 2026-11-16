<?php
namespace app\models\database;

use Override;
use core\data\DatabaseModel;

class Addresses extends DatabaseModel {
    protected ?int $addressId = null;
    protected int $userId;
    protected string $province;
    protected string $city;
    protected string $barangay;
    protected string $street;
    protected string $landmark;
    protected ?string $houseNumber = null;
    protected ?string $zipCode = null;

    #[Override]
    public function primaryKey(): string|array {
        return "addressId";
    }

    #[Override]
    protected function attributes(): array {
        return ["userId", "province", "city", "barangay", "street", "landmark", "houseNumber", "zipCode"];
    }

    #[Override]
    protected function rules(): array {
        return [
            "province" => [self::RULE_REQUIRED],
            "city" => [self::RULE_REQUIRED],
            "barangay" => [self::RULE_REQUIRED],
            "street" => [self::RULE_REQUIRED],
            "landmark" => [self::RULE_REQUIRED]
        ];
    }
}