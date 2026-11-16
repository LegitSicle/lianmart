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
        $body["orders"] = $this->queryOrders();

        return Response::create()
            ->contentFrom("/admin/orders", "admin", $body);
    }

    private function queryOrders(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $rows = $sql
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
            ->innerJoin("Addresses", ["Orders.addressId" => "Addresses.addressId"])
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