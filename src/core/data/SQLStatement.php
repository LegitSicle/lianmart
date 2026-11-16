<?php
namespace core\data;

use PDO;
use PDOStatement;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

use function sprintf;
use function is_int;
use function is_string;
use function is_array;
use function is_bool;
use function is_null;
use function in_array;
use function count;

/**
 * A literal string that is bound as a parameter (never concatenated into the SQL).
 */
final class SQLValue {
    public function __construct(public readonly string $value) {}
}
 
/**
 * A CONCAT(...) / CONCAT_WS(...) expression with an optional alias.
 * Used in select() (alias required) and in where() (alias ignored).
 */
final class SQLConcat {
    public function __construct(
        public readonly array $parts,
        public readonly ?string $alias = null,
        public readonly ?string $separator = null   // set => CONCAT_WS, skips NULL/empty parts
    ) {}
}
 
/**
 * Another SQLStatement embedded as a subquery: (SELECT ...) AS alias.
 * Used in select() (alias required).
 */
final class SQLSubquery {
    public function __construct(
        public readonly SQLStatement $statement,
        public readonly ?string $alias = null
    ) {}
}
 
class SQLStatement {
    // Aggregates
    public const GROUP_CONCAT = "GROUP_CONCAT(%val)";
    public const COUNT = "COUNT(%val)";
    public const SUM = "SUM(%val)";
    public const MIN = "MIN(%val)";
    public const MAX = "MAX(%val)";
    public const AVG = "AVG(%val)";
 
    // Order directions
    public const ASC = "ASC";
    public const DESC = "DESC";
 
    // Arithmetic
    public const ADD      = "+";
    public const SUBTRACT = "-";
    public const MULTIPLY = "*";
    public const DIVIDE   = "/";
 
    public const THIS_WEEK  = "THIS_WEEK";
    public const LAST_WEEK  = "LAST_WEEK";
    public const THIS_MONTH = "THIS_MONTH";
    public const LAST_MONTH = "LAST_MONTH";
    public const THIS_YEAR  = "THIS_YEAR";
    public const LAST_YEAR  = "LAST_YEAR";
 
    private const ARITHMETIC = [self::ADD, self::SUBTRACT, self::MULTIPLY, self::DIVIDE];
    private const AGGREGATES = [
        self::GROUP_CONCAT,
        self::COUNT,
        self::SUM,
        self::MIN,
        self::MAX,
        self::AVG
    ];
    private const OPERATORS = ["=", "!=", "<>", "<", "<=", ">", ">=", "LIKE", "NOT LIKE", "IN", "NOT IN"];
 
    private string $sql = "";
    private array $data = [];
    private bool $hasSubquery = false;
    private bool $hasWhere = false;
    private bool $hasGroupBy = false;
 
    public function __construct(
        private PDO $pdo
    ) {}
 
    /**
     * A literal for use inside a concat, e.g.
     * SQLStatement::concat(["firstName", SQLStatement::value(" "), "lastName"], "fullName")
     */
    public static function value(string $value): SQLValue {
        return new SQLValue($value);
    }
 
    /**
     * CONCAT(...). Parts are column names, SQLStatement::value() literals or nested concats.
     * Returns NULL if any part is NULL.
     */
    public static function concat(array $parts, ?string $alias = null): SQLConcat {
        return new SQLConcat($parts, $alias);
    }
 
    /**
     * CONCAT_WS(...): joins with a separator and skips parts that are NULL or empty, e.g.
     * SQLStatement::concatWs(" ", ["firstName", "middleName", "lastName"], "fullName")
     *
     * A part that should only appear when a column exists can be a nested concat:
     * SQLStatement::concat([SQLStatement::value("Unit "), "unit"])
     */
    public static function concatWs(string $separator, array $parts, ?string $alias = null): SQLConcat {
        return new SQLConcat($parts, $alias, $separator);
    }
 
    /**
     * Embeds another statement in select() as a scalar subquery, e.g.
     * `SQLStatement::subquery(
     *     $sql->sub()->select([[[SQLStatement::COUNT => "*"], "n"]])->from("Products"),
     *     "totalProducts"
     * )`
     * The inner statement must be a separate instance (use sub()) and is consumed by the embedding.
     */
    public static function subquery(self $statement, ?string $alias = null): SQLSubquery {
        return new SQLSubquery($statement, $alias);
    }
 
    /**
     * A fresh, empty statement on the same connection, for building subqueries.
     */
    public function sub(): self {
        return new self($this->pdo);
    }
 
    public function getSql(): string {
        return $this->sql;
    }
 
    /**
     * Entries:<br>
     *   "col"                                          => `col`<br>
     *   "col" => "alias"                               => `col` AS `alias`<br>
     *   [[SQLStatement::COUNT => "col"], "alias"]      => COUNT(`col`) AS `alias`<br>
     *   [SQLStatement::COUNT => "col"]                 => COUNT(`col`)  (alias is optional)<br>
     *   [[SQLStatement::MULTIPLY => ["a", "b"]], "x"]  => (`a` * `b`) AS `x`<br>
     *   [[SQLStatement::MULTIPLY => ["a", 1.1]], "x"]  => (`a` * 1.1) AS `x`  (numbers are allowed as operands)<br>
     *   SQLStatement::concat([...], "alias")           => CONCAT(...) AS `alias`<br>
     *   SQLStatement::subquery($sql->sub()->select(...)->from(...), "alias")  => (SELECT ...) AS `alias`<br>
     *
     * Aggregates and arithmetic nest:<br>
     *   [[SQLStatement::SUM => [SQLStatement::MULTIPLY => ["price", "quantity"]]], "total"]
     */
    public function select(array $columns): self {
        if ($columns === []) {
            throw new InvalidArgumentException("select() needs at least one column.");
        }
 
        $parts = [];
        $bindings = [];
        foreach ($columns as $key => $column) {
            if (is_string($key)) {
                if ($this->isTemplate($key)) {
                    throw new InvalidArgumentException(
                        "Aggregates and arithmetic must be written as [[TEMPLATE => column], alias]."
                    );
                }
                if (!is_string($column)) {
                    throw new InvalidArgumentException("The alias for '$key' must be a string.");
                }
                $parts[] = $this->encase($key) . " AS " . $this->alias($column);
            } elseif (is_string($column)) {
                $parts[] = $this->encase($column);
            } elseif ($column instanceof SQLSubquery) {
                [$sql, $exprBindings] = $this->subquerySql($column);
                $parts[] = $this->aliased($sql, $column->alias);
                array_push($bindings, ...$exprBindings);
            } elseif ($column instanceof SQLConcat) {
                [$sql, $exprBindings] = $this->concatSql($column->parts, $column->separator);
                $parts[] = $this->aliased($sql, $column->alias);
                array_push($bindings, ...$exprBindings);
            } elseif (is_array($column) && array_is_list($column) && count($column) === 2
                && is_array($column[0]) && is_string($column[1])) {
                [$sql, $exprBindings] = $this->expression($column[0]);
                $parts[] = $sql . " AS " . $this->alias($column[1]);
                array_push($bindings, ...$exprBindings);
            } elseif (is_array($column) && count($column) === 1 && !array_is_list($column)) {
                [$sql, $exprBindings] = $this->expression($column);
                $parts[] = $sql;
                array_push($bindings, ...$exprBindings);
            } else {
                throw new InvalidArgumentException("Invalid select entry at position " . var_export($key, true) . ".");
            }
        }
 
        $this->sql .= "SELECT " . implode(", ", $parts) . " ";
        array_push($this->data, ...$bindings);
        return $this;
    }
 
    public function selectExists(string $table): self {
        $this->sql .= "SELECT EXISTS (SELECT 1 FROM " . $this->encase($table) . " ";
        $this->hasSubquery = true;
        return $this;
    }
 
    public function selectAll(): self {
        $this->sql .= "SELECT * ";
        return $this;
    }
 
    public function deleteFrom(string $table): self {
        $this->sql .= "DELETE " . $this->encase($table) . " FROM " . $this->encase($table) . " ";
        return $this;
    }
 
    public function from(string $table): self {
        $this->sql .= "FROM " . $this->encase($table) . " ";
        return $this;
    }
 
    public function innerJoin(string $table, array $on): self {
        return $this->join("INNER", $table, $on);
    }
 
    public function leftJoin(string $table, array $on): self {
        return $this->join("LEFT", $table, $on);
    }
 
    /**
     * @param string|SQLConcat $column A column name, or SQLStatement::concat()/concatWs() (alias ignored).
     */
    public function where(string|SQLConcat $column, string $operator, mixed $value): self {
        return $this->condition("AND", $column, $operator, $value);
    }
 
    public function orWhere(string|SQLConcat $column, string $operator, mixed $value): self {
        return $this->condition("OR", $column, $operator, $value);
    }
 
    /**
     * Rows whose date/datetime column falls in the given period, e.g.<br>
     * `->wherePeriod("Orders.created_at", SQLStatement::LAST_MONTH)`
     * Uses the database server's clock and compares as a range, so indexes on the column still work.
     */
    public function wherePeriod(string $column, string $period): self {
        return $this->addCondition("AND", $this->periodSql($column, $period));
    }
 
    public function orWherePeriod(string $column, string $period): self {
        return $this->addCondition("OR", $this->periodSql($column, $period));
    }
 
    public function insert(string $table, array $columnValues): self {
        if ($columnValues === []) {
            throw new InvalidArgumentException("insert() needs at least one column.");
        }
 
        $columns = array_map($this->encase(...), array_keys($columnValues));
 
        $this->sql .= sprintf(
            "INSERT INTO %s (%s) VALUES (%s) ",
            $this->encase($table),
            implode(", ", $columns),
            implode(", ", array_fill(0, count($columnValues), "?"))
        );
        array_push($this->data, ...array_values($columnValues));
 
        return $this;
    }
 
    public function update(string $table, array $columnValues): self {
        if ($columnValues === []) {
            throw new InvalidArgumentException("update() needs at least one column.");
        }
 
        $sets = array_map(fn ($c) => $this->encase($c) . ' = ?', array_keys($columnValues));
 
        $this->sql .= sprintf(
            "UPDATE %s SET %s ",
            $this->encase($table),
            implode(', ', $sets)
        );
        array_push($this->data, ...array_values($columnValues));
        return $this;
    }
 
    public function groupBy(array $columns): self {
        if ($columns === []) {
            throw new InvalidArgumentException("groupBy() needs at least one column.");
        }
        if ($this->hasGroupBy) {
            throw new LogicException("groupBy() can only be called once; pass all columns in one array.");
        }
 
        $this->sql .= "GROUP BY "
            . implode(", ", array_map($this->encase(...), $columns))
            . " ";
        $this->hasGroupBy = true;
 
        return $this;
    }
 
    public function orderBy(array $columns): self {
        if ($columns === []) {
            throw new InvalidArgumentException('orderBy() needs at least one column.');
        }
        $parts = [];
        foreach ($columns as $column => $direction) {
            if (is_int($column)) {
                $column = $direction;
                $direction = self::ASC;
            }
 
            $direction = strtoupper((string) $direction);
            if ($direction !== self::ASC && $direction !== self::DESC) {
                throw new InvalidArgumentException("Invalid order direction: '$direction'");
            }
            $parts[] = $this->encase($column) . ' ' . $direction;
        }
 
        $this->sql .= 'ORDER BY ' . implode(', ', $parts) . ' ';
        return $this;
    }
 
    public function limit(int $limit): self {
        $this->sql .= "LIMIT " . max(0, $limit) . " ";
        return $this;
    }
 
    public function offset(int $offset): self {
        $this->sql .= "OFFSET " . max(0, $offset) . " ";
        return $this;
    }
 
    public function query(): PDOStatement {
        if ($this->sql === "") {
            throw new LogicException("No SQL to execute.");
        }
 
        if ($this->hasSubquery) {
            $this->sql .= ")";
        }
 
        try {
            $statement = $this->pdo->prepare($this->sql);
            if ($statement === false) {
                throw new RuntimeException("Failed to prepare statement.");
            }
 
            foreach ($this->data as $idx => $value) {
                $statement->bindValue($idx + 1, $value, $this->pdoType($value));
            }
            if (!$statement->execute()) {
                throw new RuntimeException("Failed to execute statement.");
            }
 
            return $statement;
        } finally {
            $this->reset();
        }
    }
 
    private function isTemplate(string $key): bool {
        return in_array($key, self::AGGREGATES, true) || in_array($key, self::ARITHMETIC, true);
    }
 
    /**
     * Builds a select expression.
     * A string is a column, SQLConcat is a concat, and a one-entry array is
     * [AGGREGATE => expression] or [OPERATOR => [left, right]]; these nest.
     *
     * @return array{0: string, 1: array} SQL fragment and the bindings it needs, in order.
     */
    private function expression(mixed $expr): array {
        // Numbers are written into the SQL directly; the checks below make that safe.
        if (is_int($expr) || (is_float($expr) && is_finite($expr))) {
            return [(string) $expr, []];
        }
        if (is_string($expr)) {
            if (preg_match('/^-?\d+(\.\d+)?$/', $expr)) {
                return [$expr, []];
            }
            return [$this->encase($expr), []];
        }
        if ($expr instanceof SQLValue) {
            return ["?", [$expr->value]];
        }
        if ($expr instanceof SQLConcat) {
            return $this->concatSql($expr->parts, $expr->separator);
        }
        if ($expr instanceof SQLSubquery) {
            return $this->subquerySql($expr);
        }
        if (is_array($expr) && count($expr) === 1) {
            $template = array_key_first($expr);
            $arg = $expr[$template];
 
            if (is_string($template) && in_array($template, self::AGGREGATES, true)) {
                [$inner, $bindings] = $this->expression($arg);
                return [str_replace("%val", $inner, $template), $bindings];
            }
 
            if (is_string($template) && in_array($template, self::ARITHMETIC, true)) {
                if (!is_array($arg) || !array_is_list($arg) || count($arg) !== 2) {
                    throw new InvalidArgumentException("Operator $template needs exactly two operands: [$template => [left, right]].");
                }
                [$left, $leftBindings] = $this->expression($arg[0]);
                [$right, $rightBindings] = $this->expression($arg[1]);
                return ["($left $template $right)", [...$leftBindings, ...$rightBindings]];
            }
        }
        throw new InvalidArgumentException("Invalid select expression.");
    }
 
    private function aliased(string $sql, ?string $alias): string {
        return $alias === null ? $sql : $sql . " AS " . $this->alias($alias);
    }
 
    private function alias(string $alias): string {
        $alias = trim($alias);
        if (!preg_match("/^[A-Za-z_][A-Za-z0-9_]*$/", $alias)) {
            throw new InvalidArgumentException("Invalid alias: $alias");
        }
        return "`$alias`";
    }
 
    private function condition(string $proposition, string|SQLConcat $column, string $operator, mixed $value): self {
        [$sql, $bindings] = $this->conditionSql($column, $operator, $value);
        return $this->addCondition($proposition, $sql, $bindings);
    }
 
    /**
     * Builds one condition such as `col` = ? without attaching it to the statement.
     *
     * @return array{0: string, 1: array} SQL fragment and the bindings it needs, in order.
     */
    private function conditionSql(string|SQLConcat $column, string $operator, mixed $value): array {
        $operator = strtoupper($operator);
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException("Invalid operator: {$operator}");
        }
        [$column, $columnBindings] = $this->columnExpression($column);
 
        if ($value instanceof self) {
            if ($operator === "LIKE" || $operator === "NOT LIKE") {
                throw new InvalidArgumentException("Operator $operator cannot be used with a subquery.");
            }
            [$subSql, $subBindings] = $this->statementSql($value);
            return ["$column $operator $subSql", [...$columnBindings, ...$subBindings]];
        }
 
        if (is_null($value)) {
            return match ($operator) {
                "="        => ["$column IS NULL", $columnBindings],
                "!=", "<>" => ["$column IS NOT NULL", $columnBindings],
                default    => throw new InvalidArgumentException("Operator $operator cannot be used with NULL."),
            };
        }
        if (is_array($value)) {
            if ($value === []) {
                throw new InvalidArgumentException("$operator needs at least one value.");
            }
            return [
                "$column $operator (" . implode(", ", array_fill(0, count($value), "?")) . ")",
                [...$columnBindings, ...array_values($value)]
            ];
        }
        return ["$column $operator ?", [...$columnBindings, $value]];
    }
 
    private function subquerySql(SQLSubquery $subquery): array {
        return $this->statementSql($subquery->statement);
    }
 
    /**
     * Takes the SQL and bindings built so far on another statement and wraps them in parentheses.
     * The inner statement is cleared afterwards, like query() does.
     *
     * @return array{0: string, 1: array} SQL fragment and the bindings it needs, in order.
     */
    private function statementSql(self $inner): array {
        if ($inner === $this) {
            throw new LogicException("A statement cannot be used as a subquery of itself; use sub().");
        }
 
        $sql = trim($inner->sql);
        if ($sql === "" || !str_starts_with(strtoupper($sql), "SELECT")) {
            throw new InvalidArgumentException("A subquery must be a SELECT statement.");
        }
        if ($inner->hasSubquery) {
            $sql .= ")";
        }
        $bindings = $inner->data;
        $inner->reset();
 
        return ["($sql)", $bindings];
    }
 
    private function reset(): void {
        $this->sql = "";
        $this->data = [];
        $this->hasWhere = false;
        $this->hasGroupBy = false;
        $this->hasSubquery = false;
    }
 
    /**
     * The column side of a condition: a column name or a concat.
     *
     * @return array{0: string, 1: array} SQL fragment and the bindings it needs, in order.
     */
    private function columnExpression(string|SQLConcat $column): array {
        if (is_string($column)) {
            return [$this->encase($column), []];
        }
        return $this->concatSql($column->parts, $column->separator);
    }
 
    /**
     * Builds CONCAT(...) or, with a separator, CONCAT_WS(...). Column parts are wrapped in
     * NULLIF(col, '') in CONCAT_WS mode (and in concats nested inside it) so empty strings
     * are skipped like NULLs.
     *
     * @return array{0: string, 1: array} SQL fragment and the bindings it needs, in order.
     */
    private function concatSql(array $parts, ?string $separator = null, bool $skipEmpty = false): array {
        $minimum = $separator === null ? 2 : 1;
        if (count($parts) < $minimum) {
            throw new InvalidArgumentException("A concat needs at least $minimum part(s).");
        }
        $skipEmpty = $skipEmpty || $separator !== null;
 
        $sql = [];
        $bindings = [];
        if ($separator !== null) {
            $sql[] = "?";
            $bindings[] = $separator;
        }
 
        foreach ($parts as $part) {
            if ($part instanceof SQLValue) {
                $sql[] = "?";
                $bindings[] = $part->value;
            } elseif ($part instanceof SQLConcat) {
                [$nestedSql, $nestedBindings] = $this->concatSql($part->parts, $part->separator, $skipEmpty);
                $sql[] = $nestedSql;
                array_push($bindings, ...$nestedBindings);
            } elseif (is_string($part)) {
                $column = $this->encase($part);
                $sql[] = $skipEmpty ? "NULLIF($column, '')" : $column;
            } else {
                throw new InvalidArgumentException("Concat parts must be column names, SQLStatement::value() or a concat.");
            }
        }
 
        $function = $separator !== null ? "CONCAT_WS" : "CONCAT";
        return ["$function(" . implode(", ", $sql) . ")", $bindings];
    }
 
    private function periodSql(string $column, string $period): string {
        $column = $this->encase($column);
 
        $weekStart  = "DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        $monthStart = "DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(CURDATE()) - 1 DAY)";
        $yearStart  = "DATE_SUB(CURDATE(), INTERVAL DAYOFYEAR(CURDATE()) - 1 DAY)";
 
        [$from, $to] = match ($period) {
            self::THIS_WEEK  => [$weekStart, "DATE_ADD($weekStart, INTERVAL 7 DAY)"],
            self::LAST_WEEK  => ["DATE_SUB($weekStart, INTERVAL 7 DAY)", $weekStart],
            self::THIS_MONTH => [$monthStart, "DATE_ADD($monthStart, INTERVAL 1 MONTH)"],
            self::LAST_MONTH => ["DATE_SUB($monthStart, INTERVAL 1 MONTH)", $monthStart],
            self::THIS_YEAR  => [$yearStart, "DATE_ADD($yearStart, INTERVAL 1 YEAR)"],
            self::LAST_YEAR  => ["DATE_SUB($yearStart, INTERVAL 1 YEAR)", $yearStart],
            default          => throw new InvalidArgumentException("Invalid period: $period"),
        };
 
        return "($column >= $from AND $column < $to)";
    }
 
    private function encase(string $name): string {
        $parts = explode(".", trim($name));
 
        foreach ($parts as $part) {
            if ($part !== "*" && !preg_match("/^[A-Za-z_][A-Za-z0-9_]*$/", $part)) {
                throw new InvalidArgumentException("Invalid identifier: $name");
            }
        }
 
        return implode(".", array_map(fn ($p) => $p === "*" ? "*" : "`$p`", $parts));
    }
 
    private function join(string $type, string $table, array $on): self {
        if ($on === []) {
            throw new InvalidArgumentException("A join needs at least one ON condition.");
        }
 
        $conditions = [];
        foreach ($on as $first => $second) {
            $conditions[] = $this->encase($first) . " = " . $this->encase($second);
        }
 
        $this->sql .= sprintf("%s JOIN %s ON %s ", $type, $this->encase($table), implode(" AND ", $conditions));
        return $this;
    }
 
    private function pdoType(mixed $value): int {
        return match (true) {
            is_int($value)  => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default         => PDO::PARAM_STR,
        };
    }
 
    private function addCondition(string $proposition, string $sql, array $bindings = []): self {
        $this->sql .= ($this->hasWhere ? "$proposition " : "WHERE ") . $sql . " ";
        $this->hasWhere = true;
        array_push($this->data, ...$bindings);
        return $this;
    }
}