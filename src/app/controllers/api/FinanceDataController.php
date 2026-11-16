<?php
namespace app\controllers\api;

use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\AJAXRequest;
use core\web\Request;
use core\web\Response;

class FinanceDataController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $ajax = AJAXRequest::receive();
        $ajaxData = $ajax->getJson();

        if (empty($ajaxData["option"]) || $ajaxData["option"] > 3 || $ajaxData["option"] < 1) {
            return Response::sendJson([
                "success" => false
            ]);
        }
        
        $path = $request->path();
        if ($path === "/api/admin/finance/orders") {
            return Response::sendJson([
                "success" => true,
                "made" => $this->queryOrdersMade($ajaxData["option"])
            ]);
        }

        if ($path === "/api/admin/finance/sales") {
            return Response::sendJson([
                "success" => true,
                ...$this->querySalesOverview($ajaxData["option"])
            ]);
        }

        if ($path === "/api/admin/finance/head") {
            return Response::sendJson([
                "success" => true,
                ...$this->queryHeadInfo($ajaxData["option"])
            ]);
        }

        return Response::sendJson([
            "success" => false
        ]);
    }

    private function queryHeadInfo(int $option): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
    
        [$period, $lastPeriod] = match ($option) {
            1 => [SQLStatement::THIS_WEEK,  SQLStatement::LAST_WEEK],
            2 => [SQLStatement::THIS_MONTH, SQLStatement::LAST_MONTH],
            3 => [SQLStatement::THIS_YEAR,  SQLStatement::LAST_YEAR],
        };
    
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
            foreach (["total{$name}" => $period, "lastTotal{$name}" => $lastPeriod] as $alias => $range) {
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

    private function queryOrdersMade(int $option): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];

        $period = match ($option) {
            1 => SQLStatement::THIS_WEEK,
            2 => SQLStatement::THIS_MONTH,
            3 => SQLStatement::THIS_YEAR
        };

        return $sql
            ->select([
                "Orders.orderStatus",
                [SQLStatement::COUNT => "*"]
            ])
            ->from("Orders")
            ->wherePeriod("Orders.created_at", $period)
            ->groupBy(["Orders.orderStatus"])
            ->query()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function querySalesOverview(int $option): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];

        $period = match ($option) {
            1 => SQLStatement::THIS_YEAR,
            2 => SQLStatement::THIS_MONTH,
            3 => SQLStatement::THIS_WEEK
        };

        $rows = $sql
            ->select(["created_at", "total"])
            ->from("Orders")
            ->where("orderStatus", "=", "Completed")
            ->wherePeriod("created_at", $period)
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = match ($option) {
            1 => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            2 => array_map(
                fn ($n) => "Week $n",
                range(1, (int) ceil((int) date("t") / 7))
            ),
            3 => ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
        };
        $buckets = array_fill_keys($labels, 0.0);

        foreach ($rows as $row) {
            $time = strtotime($row["created_at"]);
            $label = match ($option) {
                1 => date("M", $time),
                2 => "Week " . (intdiv((int) date("j", $time) - 1, 7) + 1),
                3 => date("D", $time),
            };
            $buckets[$label] += (float) $row["total"];
        }

        return [
            "dates" => array_keys($buckets),
            "sales" => array_map(fn ($v) => round($v, 2), array_values($buckets)),
        ];
    }
}