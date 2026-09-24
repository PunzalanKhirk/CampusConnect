<?php
/**
 * Database
 * -------------------------------------------------
 * Thin PDO wrapper. SQL-injection defense comes entirely from
 * ALWAYS using prepared statements with bound parameters - raw string
 * concatenation into SQL is never permitted anywhere in this codebase.
 */
class Database
{
    private string $host = DB_HOST;
    private string $user = DB_USER;
    private string $pass = DB_PASS;
    private string $dbname = DB_NAME;
    private string $charset = DB_CHARSET;

    private ?PDO $pdo = null;
    private $stmt;

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            // Throw exceptions instead of silently failing/returning false
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Return associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // CRITICAL: force real prepared statements (no client-side query emulation).
            // This is what actually prevents a crafted value from altering query structure.
            PDO::ATTR_EMULATE_PREPARES => false,
            // Use native mysqlnd prepared statement handling
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Never leak DSN/credentials or raw driver errors to the browser
            error_log('[CampusConnect DB ERROR] ' . $e->getMessage());
            if (APP_ENV === 'development') {
                die('Database connection failed: ' . $e->getMessage());
            }
            die('A system error occurred. Please try again later.');
        }
    }

    /** Prepare a query. Always call with a parameterized $sql string. */
    public function query(string $sql): void
    {
        $this->stmt = $this->pdo->prepare($sql);
    }

    /**
     * Bind a single value to a named placeholder, inferring the PDO type.
     * Never accept raw, unbound values into query() - always bind() them.
     */
    public function bind(string $param, $value, ?int $type = null): void
    {
        if ($type === null) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default          => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->commit(); }
    public function rollBack(): bool { return $this->pdo->rollBack(); }
}

/**
 * Sanitize helper for XSS defense.
 * All dynamic content must be passed through this before being echoed
 * into any view. Views use the short alias e() (defined in Controller.php).
 */
function sanitize_output(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
