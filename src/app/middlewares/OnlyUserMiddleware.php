<?php
namespace app\middlewares;

use Override;
use core\controller\Middleware;
use core\web\Request;
use core\web\Response;

class OnlyUserMiddleware extends Middleware {
    #[Override]
    public function handle(Request $request, callable $next): Response {
        if (self::getShared("isAdmin")) return Response::redirect("/admin/orders");
        return $next($request);
    }
}