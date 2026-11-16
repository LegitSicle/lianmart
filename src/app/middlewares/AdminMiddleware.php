<?php
namespace app\middlewares;
use Exception;
use Override;
use core\controller\Middleware;
use core\web\Request;
use core\web\Response;

class AdminMiddleware extends Middleware {
    #[Override]
    public function handle(Request $request, callable $next): Response {
        if (!self::getShared("isAdmin")) throw new Exception("Forbidden!", 403);
        return $next($request);
    }
}