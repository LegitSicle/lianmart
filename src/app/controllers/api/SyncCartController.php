<?php
namespace app\controllers\api;

use app\models\database\CartItems;
use Override;
use PDO;
use core\controller\Controller;
use core\data\Database;
use core\data\SQLStatement;
use core\web\AJAXRequest;
use core\web\Request;
use core\web\Response;

class SyncCartController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $ajax = AJAXRequest::receive();
        $ajaxData = $ajax->getJson();
        if (empty($ajaxData["product_id"]) || empty($ajaxData["quantity"])) {
            return Response::sendJson([
                "success" => false,
                "message" => "product_id or quantity was not received!"
            ]);
        }

        if (!$this->isValidId($ajaxData["product_id"])) {
            return Response::sendJson([
                "success" => false,
                "message" => "Product is not inside user's cart!"
            ]);
        }

        if (!$this->isValidQuantity($ajaxData["product_id"], $ajaxData["quantity"])) {
            return Response::sendJson([
                "success" => false,
                "message" => "Quantity exceeds stock!"
            ]);
        }
        
        $cartItem = new CartItems();
        $cartItem->loadData([
            "userId" => $this->injectedData["userId"],
            "productId" => $ajaxData["product_id"],
            "quantity" => $ajaxData["quantity"]
        ]);
        $cartItem->update();
        return Response::sendJson([
            "success" => true,
            "message" => "Updated Cart Item!",
            ...$this->queryTotals()
        ]);
    }

    private function isValidQuantity(int $productId, int $quantity): bool {
        /** @var Database */
        $database = $this->injectedData["database"];
        $availableStock = $database->createSQL()
            ->select([
                "stock"
            ])
            ->from("Products")
            ->where("productId", "=", $productId)
            ->query()
            ->fetchColumn();
        if ($availableStock === false || $availableStock === null) {
            return false;
        }
        return $quantity >= 1 && $quantity <= (int)$availableStock;
    }

    private function isValidId(int $productId): bool {
        /** @var Database */
        $database = $this->injectedData["database"];
        return $database->createSQL()
            ->selectExists("CartItems")
            ->where("productId", "=", $productId)
            ->where("userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetchColumn();
    }

    private function queryTotals(): array {
        /** @var Database */
        $database = $this->injectedData["database"];

        $rows = $database->createSQL()
            ->select([
                "CartItems.quantity",
                [[SQLStatement::MULTIPLY => ["Products.productPrice", "CartItems.quantity"]], "subtotal"],
            ])
            ->from("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);

        return [
            "quantity" => (int) array_sum(array_column($rows, "quantity")),
            "subtotal" => (float) array_sum(array_column($rows, "subtotal")),
        ];
    }
}