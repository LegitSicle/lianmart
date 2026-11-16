<?php
namespace app\controllers;
use Exception;
use Override;
use core\controller\Controller;
use core\web\Request;
use core\web\Response;

class ErrorController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $statusCode = $request->apacheErrorStatus();
        if ($statusCode === null) throw new Exception("Route Not Found!", 404);
        if ($statusCode === 403) throw new Exception("Forbidden!", 403);
        return Response::redirectOnly("/home");
    }
}