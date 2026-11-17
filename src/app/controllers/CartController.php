<?php
namespace app\controllers;

use app\models\database\OrderItems;
use app\models\database\Orders;
use app\models\database\Products;
use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;
use core\web\Session;

class CartController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["title"] = "Checkout - LianMart";
        $body["username"] = $this->injectedData["username"];
        $body["isAdmin"] = $this->injectedData["isAdmin"];
        $this->deleteOutofStockItems();
        if ($request->isGet()) {
            return $this->respond($body);
        }

        // POST Request
        if (isset($body["action"])) {
            if ($body["action"] === "delete") {
                foreach ($body["products"] as $productId) {
                    $this->deleteCartItem($productId);
                }
            } else if ($body["action"] === "checkout" && isset($body["products"])) {
                $userId = $this->injectedData["userId"];
                $order = new Orders();
                $order->loadData([
                    "addressId" => $this->queryAddressId($userId),
                    "userId" => $userId,
                    "total" => $this->querySubtotal()
                ]);
                $order->insert();

                foreach ($body["products"] as $productId) {
                    $orderInfo = $this->queryForOrderItems($productId);
                    $orderItem = new OrderItems();
                    $orderItem->loadData([
                        "orderId" => $order->orderId,
                        "productId" => $productId,
                        "quantity" => $orderInfo["quantity"],
                        "purchasePrice" => $orderInfo["productPrice"]
                    ]);
                    $orderItem->insert();

                    $product = new Products();
                    $product->loadData([
                        "productId" => $productId,
                        "stock" => $this->queryNewStock($productId)
                    ]);
                    $product->update();
                    
                    $this->deleteCartItem($productId);
                    $this->createValidRedirect();
                    return Response::redirect("/ordered");
                }
            }
        }
        return $this->respond($body);
    }

    private function createValidRedirect(): void {
        /** @var Session */
        $session = $this->injectedData["session"];
        $session->setFlash("isValidRedirect", true);
    }

    private function queryAddressId(int $userId): int {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select(["Addresses.addressId"])
            ->from("Addresses")
            ->where("Addresses.userId", "=", $userId)
            ->orderBy(["Addresses.addressId"])
            ->limit(1)
            ->query()
            ->fetchColumn();
    }

    private function queryNewStock(int $productId): int {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                [SQLStatement::SUBTRACT => ["Products.stock", "CartItems.quantity"]]
            ])
            ->from("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->where("Products.productId", "=", $productId)
            ->query()
            ->fetchColumn();
    }

    private function querySubtotal(): float {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $subtotals = $sql
            ->select([
                [SQLStatement::MULTIPLY => ["Products.productPrice", "CartItems.quantity"]]
            ])
            ->from("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetchAll(PDO::FETCH_COLUMN);
        return (float) array_sum($subtotals);
    }

    private function queryForOrderItems(int $productId): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                "CartItems.quantity",
                "Products.productPrice"
            ])
            ->from("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->where("CartItems.productId", "=", $productId)
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetch(PDO::FETCH_ASSOC);
    }

    private function queryCartItems(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                "Products.productId",
                "Products.productName",
                "Products.productDetails",
                "Products.productPrice",
                "Products.stock",
                "CartItems.quantity",
                "ProductImages.imagePath"
            ])
            ->from("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->leftJoin("ProductImages", ["Products.productId" => "ProductImages.productId"])
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function deleteOutofStockItems(): void {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql
            ->deleteFrom("CartItems")
            ->innerJoin("Products", ["CartItems.productId" => "Products.productId"])
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->where("Products.stock", "<=", 0)
            ->query();
    }

    private function deleteCartItem(int $productId): void {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql
            ->deleteFrom("CartItems")
            ->where("productId", "=", $productId)
            ->where("userId", "=", $this->injectedData["userId"])
            ->query();
    }

    private function respond(array $body): Response {
        $body["cartItems"] = $this->queryCartItems();
        $body["subtotal"] = $this->querySubtotal();
        return Response::create()
            ->contentFrom("/cart", "main", $body);
    }
}