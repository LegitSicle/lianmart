<?php
namespace app\middlewares;
use Override;
use core\controller\Middleware;
use core\web\Request;
use core\web\Response;

class RedirectMiddleware extends Middleware {
    public function __construct(
        private string $redirectURL
    ) {}

    #[Override]
    public function handle(Request $request, callable $next): Response {
        $isValidRedirect = self::getShared("session")->getFlash("isValidRedirect");
        if (empty($isValidRedirect) || !$isValidRedirect) {
            return Response::redirectOnly($this->redirectURL);
        }
        return $next($request);
    }
}