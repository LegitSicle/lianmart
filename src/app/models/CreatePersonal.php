<?php
namespace app\models;

use Override;
use core\data\Model;

class CreatePersonal extends Model {
    protected string $firstname;
    protected string $lastname;
    protected ?string $birthdate = null;
    protected string $phoneNumber;

    #[Override]
    protected function rules(): array {
        return [
            "firstname" => [self::RULE_REQUIRED],
            "lastname" => [self::RULE_REQUIRED],
            "birthdate" => [self::RULE_DATE],
            "phoneNumber" => [self::RULE_REQUIRED, self::RULE_LEN_EQUALS => 10]
        ];
    }

    #[Override]
    protected function errorMessage(): array {
        $errors = parent::errorMessage();
        $errors[self::RULE_LEN_EQUALS] = "Invalid Phone Number!";
        return $errors;
    }
}