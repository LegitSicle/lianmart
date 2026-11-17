<?php
// entry point of the website
declare(strict_types=1);
define("ROOT", dirname(__DIR__));
require_once ROOT . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once cleanPath(ROOT . "/src/core/bootstrap.php");

use app\controllers\AuthController;
use app\controllers\PageController;
use app\middlewares\AdminMiddleware;
use app\middlewares\DisabledUserMiddleware;
use app\middlewares\LoggedInMiddleware;
use app\middlewares\OnlyUserMiddleware;
use app\middlewares\RedirectMiddleware;
use app\middlewares\ValidProductMiddleware;
use core\Application;

$app = new Application();
PageController::insertShared("username", $app->getUsername());
AuthController::insertShared("session", $app->session);

AdminMiddleware::insertShared("isAdmin", $app->isUserPresentIn("Admins.userId"));
DisabledUserMiddleware::insertShared("isDisabled", $app->isUserPresentIn("DisabledUsers.userId"));
OnlyUserMiddleware::insertShared("isAdmin", $app->isUserPresentIn("Admins.userId"));
LoggedInMiddleware::insertShared("isGuest", $app->isGuest());
RedirectMiddleware::insertShared("session", $app->session);
ValidProductMiddleware::insertShared("sql", $app->database->createSQL());
$app->run();