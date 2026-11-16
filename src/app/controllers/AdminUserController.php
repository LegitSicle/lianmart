<?php
namespace app\controllers;

use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;

class AdminUserController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["section"] = "Customers";
        $body["users"] = $this->queryUsers($body["search"]);
        return Response::create()->contentFrom($request->path(), "admin", $body);
    }

    private function queryUsers(?string $search): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql = $sql->select([
                "firstname",
                "lastname",
                "email",
                "Users.created_at",                
                "DisabledUsers.userId" => "disabledId",
                "Admins.userId" => "adminId"
            ])
            ->from("Users")
            ->leftJoin("DisabledUsers", ["Users.userId" => "DisabledUsers.userId"])
            ->leftJoin("Admins", ["Users.userId" => "Admins.userId"]);
            
        if (isset($search)) {
            $sql = $sql->orWhere("firstname", "LIKE", "%{$search}%")
                ->orWhere("lastname", "LIKE", "%{$search}%")
                ->orWhere(SQLStatement::concat(["firstname", SQLStatement::value(" "), "lastname"]), "LIKE", "%{$search}%")
                ->orWhere(SQLStatement::concat(["firstname", "lastname"]), "LIKE", "%" . str_replace(" ", "", $search) . "%");
        }

        $rows = $sql->query()
            ->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as &$row) {
            $row["role"] = match (true) {
                $row["adminId"] !== null => "Admin",
                default => "User"
            };

            $row["status"] = match (true) {
                $row["disabledId"] !== null => "Disabled",
                default => "Active"
            };
            unset($row["disabledId"], $row["adminId"]);
        }
        return $rows;
    }
}