<?php
namespace app\controllers;

use core\controller\Controller;
use core\web\Request;
use core\web\Response;
use Override;
use PDO;
use core\data\Database;

class PageController extends Controller {
    private const TITLES = [
        "home" => "Home - Lianmart",
        "cart" => "Checkout - LianMart",
        "ordered" => "Order Confirmed - LianMart",
        "delivery" => "Delivery - LianMart",
        "contact" => "Contact Us - LianMart"
    ];

    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["title"] = self::TITLES[$body["url"]] ?? "LianMart";
        $body["username"] = static::getShared("username");
        $flash = $this->checkFlash();
        if (isset($flash)) {
            $body["show"] = true;
            $body["message"] = $flash;
        }
        if ($request->path() === "/home") {
            $body = $this->injectProducts($body);
        }
        if ($request->path() === "/ordered") {
            return Response::create()
                ->contentFrom("/ordered", "footer-only", $body);
        }
        return Response::create()
            ->contentFrom($request->path(), "main", $body);
    }

    private function checkFlash(): ?string {
        if (!isset($this->injectedData["session"])) return null;
        $session = $this->injectedData["session"];
        $warning = $session->getFlash("message");
        return $warning ?: null;
    }

    private function injectProducts(array $body): array {
        /** @var Database */
        $database = $this->injectedData["database"];
        $products = $database->createSQL()
            ->select([
                "Products.productName",
                "Products.productPrice",
                "ProductImages.imagePath"
            ])
            ->from("Products")
            ->leftJoin("ProductImages", ["Products.productId" => "ProductImages.productId"])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
        $body["products"] = $products;
        return $body;
    }
}