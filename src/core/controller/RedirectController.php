<?php
namespace core\controller;

use core\web\Request;
use core\web\Response;
use Override;

class RedirectController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        return Response::redirectOnly($this->injectedData["destination"] ?? "");
    }
}