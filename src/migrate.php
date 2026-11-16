<?php
define("ROOT", dirname(__DIR__));
require_once ROOT . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once cleanPath(ROOT . "/src/core/bootstrap.php");
use core\data\Database;

$dbConfig = [
    "migrations" => $_ENV["DB_MIGRATIONS"],
    "host" => $_ENV["DB_HOST"],
    "port" => $_ENV["DB_PORT"],
    "name" => $_ENV["DB_NAME"],
    "user" => $_ENV["DB_USER"],
    "password" => $_ENV["DB_PASSWORD"],
];
if (in_array("--init_db", $argv, true)) {
    Database::initialize($dbConfig);
}
$database = new Database($dbConfig);
$database->migrate();