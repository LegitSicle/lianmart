<?php
namespace app\controllers;

use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;

class DetailsController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["title"] = "Product Details - Lianmart";
        $body = [...$body, ...$this->queryProduct($body["id"])];
        return Response::create()
            ->contentFrom("product-details", "main", $body);
    }

    private function queryProduct(int $productId): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                "Products.productName",
                "Products.productDetails",
                "Products.productPrice",
                "Products.stock",
                "ProductImages.imagePath"
            ])
            ->from("Products")
            ->leftJoin("ProductImages", ["Products.productId" => "ProductImages.productId"])
            ->where("Products.productId", "=", $productId)
            ->limit(1)
            ->query()
            ->fetch(PDO::FETCH_ASSOC);
    }
}