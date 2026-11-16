<?php
namespace app\middlewares;

use Override;
use core\controller\Middleware;
use core\web\Request;
use core\web\Response;

class LoggedInMiddleware extends Middleware {

    #[Override]
    public function handle(Request $request, callable $next): Response {
        if (self::getShared("isGuest")) return Response::redirectOnly("/login");
        return $next($request);
    }
}