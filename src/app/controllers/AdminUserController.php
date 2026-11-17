<?php
namespace app\controllers;

use app\models\database\DisabledUsers;
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

        if ($request->isGet()) {
            $body["section"] = "Customers";
            $body["users"] = $this->queryUsers($body["search"], $body["role"], $body["status"], $body["joined"]);
            return Response::create()
                ->contentFrom($request->path(), "admin", $body);
        }

        // POST Request
        $banned = new DisabledUsers();
        $banned->loadData([
            "userId" => $body["userId"]
        ]);
        if ($body["action"] === "ban") {
            $banned->insert();
        } else if ($body["action"] === "unban") {
            $banned->delete();
        }
        return Response::redirect("/admin/users");
    }

    private function queryUsers(
        ?string $search = null, 
        ?string $role = null, 
        ?string $status = null,
        ?string $joined = null
    ): array {
        $role ??= "all";
        $status ??= "all";

        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql = $sql->select([
                "Users.userId",
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

        if (isset($joined)) {
            $sql = $sql->whereAfter("Users.created_at", $joined, true);
        }
        if (isset($search) && $search !== "") {
            $like = "%{$search}%";
            $sql = $sql->whereGroup(function (SQLStatement $q) use ($like) {
                $q->where("firstname", "LIKE", $like)
                    ->orWhere("lastname", "LIKE", $like)
                    ->orWhere(SQLStatement::concat(["firstname", SQLStatement::value(" "), "lastname"]), "LIKE", $like)
                    ->orWhere(SQLStatement::concat(["firstname", "lastname"]), "LIKE", str_replace(" ", "", $like));
            });
        }

        $rows = $sql->query()
            ->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $key => &$row) {
            $isAdmin = $row["adminId"] !== null;
            $isDisabled = $row["disabledId"] !== null;

            if (
                ($role === 'admin' && !$isAdmin)
                || ($role === 'user' && $isAdmin)
                || ($status === 'disabled' && !$isDisabled)
                || ($status === 'active' && $isDisabled)
            ) {
                unset($rows[$key]);
                continue;
            }

            $row["role"] = $isAdmin ? "Admin" : "User";
            $row["status"] = $isDisabled ? "Disabled" : "Active";
            unset($row["disabledId"], $row["adminId"]);
        }
        return $rows;
    }
}