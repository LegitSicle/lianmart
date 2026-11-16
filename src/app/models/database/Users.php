<?php
namespace app\models\database;

use Override;
use core\data\UserModel;

class Users extends UserModel {
    protected ?int $userId = null;
    protected string $firstname;
    protected string $lastname;
    protected string $email;
    protected string $password;
    protected ?string $birthdate;
    protected string $phoneNumber;

    #[Override]
    public function insert(): void {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        parent::insert();
    }

    #[Override]
    public function primaryKey(): string {
        return "userId";
    }

    #[Override]
    protected function attributes(): array{
        return ["firstname", "lastname", "email", "password", "birthdate", "phoneNumber"];
    }

    #[Override]
    protected function rules(): array {
        return [];
    }

    #[Override]
    protected function errorMessage(): array {
        $messages = parent::errorMessage();
        $messages[self::RULE_UNIQUE] = "This Email has already been used by another user!";
        return $messages;
    }

    #[Override]
    public function displayName(): string {
        return "{$this->firstname} {$this->lastname}";
    }
}