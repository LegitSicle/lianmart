<?php
namespace app\middlewares;

use Override;
use core\controller\Middleware;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;

class ValidProductMiddleware extends Middleware {
    #[Override]
    public function handle(Request $request, callable $next): Response {
        $body = $request->body();
        $productId = $body["id"];
        if (empty($productId)) return Response::redirect("/home");

        /** @var SQLStatement */
        $sql = self::getShared("sql");
        $result = $sql
            ->selectAll()
            ->from("Products")
            ->where("productId", "=", $productId)
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (!$result) return Response::redirect("/home");
        return $next($request);
    }
}