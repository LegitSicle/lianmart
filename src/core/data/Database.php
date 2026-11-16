<?php
namespace core\data;
use PDO;
use PDOException;

class Database {
    public readonly PDO $pdo;
    private string $migrationsDir;

    public function __construct(
        array $config
    ) {
        $dsn = "mysql:host={$config["host"]};port={$config["port"]};dbname={$config["name"]}";
        $user = $config["user"] ?? "";
        $password = $config["password"] ?? "";
        $this->migrationsDir = getcwd() . $config["migrations"];
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Used for setting up the user and database specified in the `.env` file.
     * @param array $config
     * @return void
     */
    public static function initialize(array $config) {
        try {
            $user = "'{$config["user"]}'@'{$config["host"]}'";
            $pdo = new PDO("mysql:host={$config["host"]}", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config["name"]}`;");
            echo "[SUCCESS] Database '{$config["name"]}' created.\n";

            $pdo->exec("CREATE USER IF NOT EXISTS $user IDENTIFIED BY '{$config["password"]}';");
            $pdo->exec("ALTER USER $user IDENTIFIED BY '{$config["password"]}';");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$config["name"]}`.* TO $user;");
            $pdo->exec("FLUSH PRIVILEGES;");
            echo "[SUCCESS] MySQL user $user created and granted access to '{$config["name"]}'.\n";
        } catch (PDOException $e) {
            echo "[ERROR] Database Initialization Failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Applies all migration `.sql` files that have not been commited to the Migrations table yet.
     * @return void
     */
    public function migrate(): void {
        $newMigrations = [];
        $this->createMigrations();
        $applied = $this->getApplied();
        $files = scandir($this->migrationsDir);
        $toApply = array_diff($files, $applied);
        echo "[INFO] Committing migrations...\n";
        foreach ($toApply as $migration) {
            if ($migration === "." || $migration === "..") continue;
            $sql = file_get_contents($this->migrationsDir . DIRECTORY_SEPARATOR . $migration);
            $this->log("Applying $migration");
            $this->pdo->exec($sql);
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All Migrations Have Been Applied");
        }
    }

    public function createSQL(): SQLStatement {
        return new SQLStatement($this->pdo);
    }

    private function saveMigrations(array $migrations): void {
        $values = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO Migrations (migration) VALUES $values;");
        $statement->execute();
    }

    private function createMigrations(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS Migrations (
            id int AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=INNODB;");
    }

    private function getApplied(): array {
        $statement = $this->pdo->prepare("SELECT migration FROM Migrations");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function log(string $message): void {
        echo '['.date("Y-m-d H:i:s")."] - $message" . PHP_EOL;
    }
}