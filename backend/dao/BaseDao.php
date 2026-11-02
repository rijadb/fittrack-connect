<?php
/** Reusable CRUD methods. Table and column names are whitelisted by DAO subclasses. */
abstract class BaseDao
{
    protected PDO $pdo;
    protected string $table;
    /** @var string[] Columns permitted for INSERT and UPDATE, never including id. */
    protected array $writableColumns;
    /** @var string[] Columns permitted for SELECT, e.g. omit users.password_hash. */
    protected array $readableColumns;

    public function __construct(PDO $pdo, string $table, array $writableColumns, array $readableColumns)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->writableColumns = $writableColumns;
        $this->readableColumns = $readableColumns;
    }

    public function findAll(): array
    {
        $columns = implode(', ', $this->readableColumns);
        $statement = $this->pdo->query("SELECT {$columns} FROM {$this->table} ORDER BY id");
        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $columns = implode(', ', $this->readableColumns);
        $statement = $this->pdo->prepare("SELECT {$columns} FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        $record = $statement->fetch();
        return $record === false ? null : $record;
    }

    public function create(array $attributes): int
    {
        $attributes = $this->validateColumns($attributes);
        if (!$attributes) {
            throw new InvalidArgumentException('Cannot create a row without data.');
        }
        $columns = array_keys($attributes);
        $columnSql = implode(', ', $columns);
        $placeholders = implode(', ', array_map(fn(string $column) => ':' . $column, $columns));
        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} ({$columnSql}) VALUES ({$placeholders})"
        );
        $statement->execute($attributes);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $attributes): bool
    {
        $attributes = $this->validateColumns($attributes);
        if (!$attributes) {
            throw new InvalidArgumentException('Cannot update a row without data.');
        }
        $assignments = implode(', ', array_map(
            fn(string $column) => "{$column} = :{$column}", array_keys($attributes)
        ));
        $statement = $this->pdo->prepare("UPDATE {$this->table} SET {$assignments} WHERE id = :id");
        $statement->execute([...$attributes, 'id' => $id]);
        // MySQL may report zero changed rows when the new values match existing ones.
        return $statement->rowCount() > 0 || $this->findById($id) !== null;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    protected function validateColumns(array $attributes): array
    {
        foreach (array_keys($attributes) as $column) {
            if (!in_array($column, $this->writableColumns, true)) {
                throw new InvalidArgumentException("Unknown or read-only column: {$column}");
            }
        }
        return $attributes;
    }
}
