<?php
namespace app\controllers;

use app\models\database\CartItems;
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

use function in_array;

class CatalogController extends Controller {
    private const SORT_MAP = [
        "new" => ["Products.created_at" => SQLStatement::DESC],
        "asc" => ["Products.productPrice" => SQLStatement::ASC],
        "desc" => ["Products.productPrice" => SQLStatement::DESC],
        "az" => ["Products.productName" => SQLStatement::ASC]
    ];

    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();

        if ($request->isGet()) {
            $body["title"] = "Catalog - LianMart";
            $body["categories"] = $this->queryCategories();
            $body["products"] = $this->queryProducts($body);
            $body["username"] = $this->injectedData["username"];
            $body["isAdmin"] = $this->injectedData["isAdmin"];
            $body["cartedProducts"] = $this->queryCartIds();
            return Response::create()
                ->contentFrom("/catalog", "main", $body);
        }

        // POST Request
        $action = $body["action"] ?? "";
        if ($action === "" || $action === "add") {
            $cartItem = new CartItems();
            $cartItem->loadData([
                "userId" => $this->injectedData["userId"],
                ...$body
            ]);
            $cartItem->insert();
            return Response::redirectOnly("/catalog");
        } else if ($action === "buy") {
            $userId = $this->injectedData["userId"];
            $productId = $body["productId"];
            $quantity = $body["quantity"];
            [$productPrice, $stock] = $this->queryProductInfo($productId);

            $order = new Orders();
            $order->loadData([
                "addressId" => $this->queryAddressId($userId),
                "userId" => $userId,
                "total" => $productPrice * $quantity
            ]);
            $order->insert();

            $orderItem = new OrderItems();
            $orderItem->loadData([
                "orderId" => $order->orderId,
                "productId" => $productId,
                "quantity" => $quantity,
                "purchasePrice" => $productPrice
            ]);
            $orderItem->insert();

            $product = new Products();
            $product->loadData([
                "productId" => $productId,
                "stock" => $stock - $quantity
            ]);
            $product->update();

            $this->createValidRedirect();
            return Response::redirectOnly("/ordered");
        }
        return Response::redirectOnly("/catalog");
    }

    private function createValidRedirect(): void {
        /** @var Session */
        $session = $this->injectedData["session"];
        $session->setFlash("isValidRedirect", true);
    }

    private function queryProductInfo(int $productId): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select(["productPrice", "stock"])
            ->from("Products")
            ->where("productId", "=", $productId)
            ->query()
            ->fetch(PDO::FETCH_NUM);
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

    private function queryCategories(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                "categoryId",
                "categoryName"
            ])
            ->from("Categories")
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function applyFilters(SQLStatement $sql, array $body): SQLStatement {
        if (isset($body["minPrice"]) && isset($body["maxPrice"])) {
            $sql = $sql->where("Products.productPrice", ">=", $body["minPrice"])
                    ->where("Products.productPrice", "<=", $body["maxPrice"]);
        }
        if (isset($body["search"])) {
            $sql = $sql->where("Products.productName", "LIKE", "%{$body["search"]}%")
                ->orWhere("Products.productName", "LIKE", "%" . str_replace(" ", "", $body["search"]) . "%");
        }
        if (isset($body["cats"])) {
            $sql = $sql->where("Products.categoryId", "IN", explode(",", $body["cats"]));
        }
        if (isset($body["avails"])) {
            $avails = explode(",", $body["avails"]);
            if (in_array("in", $avails) && !in_array("out", $avails)) {
                $sql = $sql->where("Products.stock", ">", 0);
            } elseif (!in_array("in", $avails) && in_array("out", $avails)) {
                $sql = $sql->where("Products.stock", "=", 0);
            }
        }
        return $sql;
    }

    private function queryCartIds(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select(["CartItems.productId"])
            ->from("CartItems")
            ->where("CartItems.userId", "=", $this->injectedData["userId"])
            ->query()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private function queryProducts(array $body): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];

        $sql = $sql
            ->select([
                "Products.productId",
                "Products.productName",
                "Products.productPrice",
                "Products.stock",
                "Products.created_at",
                "ProductImages.imagePath",
            ])
            ->from("Products")
            ->leftJoin("ProductImages", ["Products.productId" => "ProductImages.productId"]);

        return $this->applyFilters($sql, $body)
            ->orderBy(self::SORT_MAP[$body["sort"] ?? "new"])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}