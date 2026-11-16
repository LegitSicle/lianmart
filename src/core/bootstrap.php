<?php
namespace core;
require_once cleanPath(ROOT . "/src/core/util/helpers.php");
use Dotenv\Dotenv;

Dotenv::createImmutable(ROOT)->load();