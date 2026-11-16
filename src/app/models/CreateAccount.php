<?php
namespace app\models;

use Override;
use core\data\DatabaseModel;

class CreateAccount extends DatabaseModel {
    protected string $email;
    protected string $password;
    protected string $confirmPassword;

    #[Override]
    protected static function table(): string {
        return "Users";
    }

    #[Override]
    public function primaryKey(): string {
        return "userId";
    }

    #[Override]
    protected function attributes(): array {
        return ["email", "password"];
    }

    #[Override]
    protected function rules(): array {
        return [
            "email" => [self::RULE_REQUIRED, self::RULE_UNIQUE],
            "password" => [self::RULE_REQUIRED, self::RULE_MIN => 8],
            "confirmPassword" => [self::RULE_REQUIRED, self::RULE_MATCH => "password"]
        ];
    }

    #[Override]
    protected function errorMessage(): array {
        $messages = parent::errorMessage();
        $messages[self::RULE_UNIQUE] = "This Email has already been used by another user!";
        return $messages;
    }
}