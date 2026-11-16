<?php
namespace app\models;

use app\models\database\Users;
use Override;
use core\data\Model;

class LoginForm extends Model {
    protected string $email = "";
    protected string $password = "";

    #[Override]
    public function rules(): array {
        return [
            "email" => [ self::RULE_REQUIRED ],
            "password" => [ self::RULE_REQUIRED ]
        ];
    }

    public function attemptLogin(): Users|bool {
        $user = Users::findOne(["email", "=", $this->email]);
        if (!$user) {
            $this->addError("email", "User does not exist with this email!");
            return false;
        }
        if (!password_verify($this->password, $user->password)) {
            $this->addError("password", "Password is incorrect!");
            return false;
        }
        return $user;
    }
}