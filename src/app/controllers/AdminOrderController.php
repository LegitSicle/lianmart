<?php
namespace app\controllers;

use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;

class AdminOrderController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["section"] = "Orders";
        $body["orders"] = $this->queryOrders($body["search"]);

        return Response::create()
            ->contentFrom("/admin/orders", "admin", $body);
    }

    private function queryOrders(?string $search): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql = $sql
            ->select([
                "Orders.orderId",
                SQLStatement::concat([
                    "Users.firstname", 
                    SQLStatement::value(" "), 
                    "Users.lastname"
                ], "customerName"),
                "Orders.total",
                "Orders.orderStatus",
                SQLStatement::concat([
                    "Addresses.street", 
                    SQLStatement::value(", "), 
                    "Addresses.barangay", 
                    SQLStatement::value(", "),
                    "Addresses.city",
                    SQLStatement::value(", "),
                    "Addresses.province",
                ], "address"),
                "Addresses.houseNumber",
                "Orders.created_at"
            ])
            ->from("Orders")
            ->innerJoin("Users", ["Orders.userId" => "Users.userId"])
            ->innerJoin("Addresses", ["Orders.addressId" => "Addresses.addressId"]);

        if (isset($search)) {
            if (str_starts_with($search, "#")) {
                $search = ltrim($search, "#");
                $sql = $sql
                    ->where("Orders.orderId", "LIKE", "%{$search}%");
            } else {
                $sql = $sql->orWhere("firstname", "LIKE", "%{$search}%")
                    ->orWhere("lastname", "LIKE", "%{$search}%")
                    ->orWhere(SQLStatement::concat(["firstname", SQLStatement::value(" "), "lastname"]), "LIKE", "%{$search}%")
                    ->orWhere(SQLStatement::concat(["firstname", "lastname"]), "LIKE", "%" . str_replace(" ", "", $search) . "%");
            }
        }

        $rows = $sql
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (isset($row["houseNumber"])) {
                $row["address"] = "{$row["houseNumber"]} {$row["address"]}";
            }
            unset($row["houseNumber"]);
        }
        return $rows;
    }
}