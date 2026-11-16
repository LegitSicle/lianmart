<?php
namespace app\middlewares;

use core\controller\Middleware;
use core\web\Request;
use core\web\Response;
use Exception;
use Override;

class DisabledUserMiddleware extends Middleware {
    #[Override]
    public function handle(Request $request, callable $next): Response {
        if (self::getShared("isDisabled")) throw new Exception("Disabled User!", 403);
        return $next($request);
    }
}