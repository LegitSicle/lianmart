<?php
namespace app\controllers;

use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;

class AdminFinanceController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $body["section"] = "Finances";
        $body["daysPassed"] = (int) date('w');
        $body = [...$body, ...$this->queryHeadInfo()];

        return Response::create()
            ->contentFrom("/admin/finances", "admin", $body);
    }

    private function queryHeadInfo(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
    
        $count   = [SQLStatement::COUNT => "*"];
        $revenue = [SQLStatement::SUM => "total"];
    
        $customersOnly = fn (SQLStatement $q) => $q
            ->leftJoin("Admins", ["Users.userId" => "Admins.userId"])
            ->leftJoin("DisabledUsers", ["Users.userId" => "DisabledUsers.userId"])
            ->where("Admins.userId", "=", null)
            ->where("DisabledUsers.userId", "=", null);
        $completedOnly = fn (SQLStatement $q) => $q->where("orderStatus", "=", "Completed");
    
        $metrics = [
            "Products"  => ["Products", $count,   null],
            "Orders"    => ["Orders",   $count,   null],
            "Customers" => ["Users",    $count,   $customersOnly],
            "Revenue"   => ["Orders",   $revenue, $completedOnly],
        ];
    
        $columns = [];
        foreach ($metrics as $name => [$table, $aggregate, $filter]) {
            foreach (["total{$name}" => SQLStatement::THIS_WEEK, "lastTotal{$name}" => SQLStatement::LAST_WEEK] as $alias => $range) {
                $inner = $sql
                    ->sub()
                    ->select([$aggregate])
                    ->from($table);
                if ($filter !== null) {
                    $inner = $filter($inner);
                }
                $columns[] = SQLStatement::subquery(
                    $inner->wherePeriod("{$table}.created_at", $range),
                    $alias
                );
            }
        }
    
        return $sql
            ->select($columns)
            ->query()
            ->fetch(PDO::FETCH_ASSOC);
    }
}