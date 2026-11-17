<?php
namespace core\data;

use Exception;
use Override;
use PDOException;
use ReflectionClass;
use ReflectionProperty;
use function is_string;
use function is_array;

/**
 * This extends the `Model` class and allows data to be inserted into the MySQL database.
 */
abstract class DatabaseModel extends Model {
    protected static Database $database;

    /**
     * Makes a property unable to be inserted into the database if it is already a value for another.<br>
     * `[..., SELF::RULE_UNIQUE]`<br>
     * `[..., SELF::RULE_UNIQUE => ["userId" => 1]]` exludes the param
     * @var string
     */
    protected const RULE_UNIQUE = "unique";

    protected static function table(): string {
        return (new ReflectionClass(static::class))->getShortName();
    }
    
    abstract protected function attributes(): array;
    abstract public function primaryKey(): string|array;

    public function insert(): void {
        try {
            $tableName = $this->table();
            $attributes = $this->attributes();
            $columnValues = [];
            foreach ($attributes as $attribute) {
                if (!$this->isInitialized($attribute)) {
                    $property = new ReflectionProperty($this, $attribute);
                    if (!$property->getType()?->allowsNull()) continue;
                    $columnValues[$attribute] = null;
                    continue;
                }
                $columnValues[$attribute] = $this->{$attribute};
            }
            static::$database->createSQL()
                ->insert($tableName, $columnValues)
                ->query();
            $primaryKey = $this->primaryKey();
            if (is_string($primaryKey) && (!$this->isInitialized($primaryKey) || $this->{$primaryKey} === null)) {
                $this->{$primaryKey} = static::$database->pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new Exception("Failed To Insert Into Database!", 500);
        }
    }

    public function update(): void {
        try {
            $tableName = $this->table();
            $columnValues = [];
            foreach ($this->attributes() as $attribute) {
                if (!$this->isInitialized($attribute)) continue;
                $columnValues[$attribute] = $this->{$attribute};
            }

            if ($columnValues === []) {
                throw new Exception("No initialized attributes to update", 500);
            }

            $primaryKeyName = $this->primaryKey();
            $sql = static::$database->createSQL()
                ->update($tableName, $columnValues);

            foreach ((array) $primaryKeyName as $primary) {
                if (!$this->isInitialized($primary) || $this->{$primary} === null) {
                    throw new Exception("Primary Key {$primary} is Null For Update", 500);
                }
                $sql = $sql->where($primary, "=", $this->{$primary});
            }
            $sql->query();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed To Update Into Database!", 500);
        }
    }

    public function delete(): void {
        try {
            $tableName = $this->table();
            $primaryKeys = [];
            $primaryKeyName = $this->primaryKey();

            if (is_string($primaryKeyName)) {
                $this->listAttributes($primaryKeys, $primaryKeyName, "Primary Key {$primaryKeyName} is Null For Deletion");
            } else if (is_array($primaryKeyName)) {
                foreach ($primaryKeyName as $name) {
                    $this->listAttributes($primaryKeys, $name, "Primary Key {$name} is Null For Deletion");
                }
            }

            if (empty($primaryKeys)) {
                throw new Exception("No primary keys found for deletion!", 500);
            }

            $sql = static::$database->createSQL()
                ->deleteFrom($tableName);

            foreach ($primaryKeys as $name => $value) {
                $sql = $sql->where($name, "=", $value);
            }

            $sql->query();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed To Delete From Database!", 500);
        }
    }

    /**
     * Similar to `validate()` of its parent class but this allows access to the database allowing it to check values.
     * @return bool Whether there was a rule broken.
     */
    #[Override]
    public function validate(): bool {
        $this->parseSelf(function (string $ruleName, string $attribute, mixed $param, mixed $value) {
            $this->defaultChecker($ruleName, $attribute, $param, $value);
            if ($ruleName === self::RULE_UNIQUE) {
                $tableName = $this->table();
                $sql = static::$database->createSQL()
                    ->selectExists($tableName)
                    ->where($attribute, "=", $value);
                if ($param !== null) {
                    foreach($param as $column => $colValue) {
                        $sql = $sql->where($column, "!=", $colValue);
                    }
                }
                if ($sql->query()->fetchColumn()) {
                    $this->addErrorForRule($attribute, self::RULE_UNIQUE);
                }
            }
        });
        return empty($this->errors);
    }

    #[Override]
    protected function errorMessage(): array {
        $messages = parent::errorMessage();
        $messages[self::RULE_UNIQUE] = "This field already exists!";
        return $messages;
    }

    private function listAttributes(array &$list, string $name, ?string $error = null): void {
        if (!$this->isInitialized($name) || $this->{$name} === null) {
            throw new Exception($error ?? "{$name} isn't initialized!", 500);
        }
        $list[$name] = $this->{$name};
    }

    private function isInitialized(string $property): bool {
        return (new ReflectionProperty($this, $property))->isInitialized($this);
    }

    public static function findOne(array ...$where): static|bool {
        $tableName = static::table();
        $sqlStatement = static::$database->createSQL()
            ->selectAll()
            ->from($tableName);
        foreach ($where as $whereCondition) {
            $sqlStatement = $sqlStatement->where($whereCondition[0], $whereCondition[1], $whereCondition[2]);
        }
        return $sqlStatement->query()->fetchObject(static::class);
    }

    public static function connectDatabase(Database $database): void {
        static::$database = $database;
    }
}