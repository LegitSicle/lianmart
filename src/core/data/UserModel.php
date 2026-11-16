<?php
namespace core\data;

abstract class UserModel extends DatabaseModel {
    abstract public function displayName(): string;
}