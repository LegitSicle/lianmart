<?php
namespace app\models\database;

use core\data\DatabaseModel;
use Override;

class DisabledUsers extends DatabaseModel {
    protected int $userId;

    #[Override]
    protected function attributes(): array {
        return ["userId"];
    }

    #[Override]
    public function primaryKey(): string|array {
        return "userId";
    }

    #[Override]
    protected function rules(): array {
        return [];
    }
}