<?php
namespace service\database;

class DatabaseManager
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(string $table, array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array_values($data));
    }

    public function insertOnDuplicateUpdate(string $table, array $data): bool
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid data format');
        }
    
        if (!is_array(reset($data))) {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
    
            $updateValues = [];
            $updateParams = [];
            foreach ($data as $column => $value) {
                $updateValues[] = $column . ' = VALUES(' . $column . ')';
                $updateParams[] = $value;
            }
            $updateValuesString = implode(', ', $updateValues);
    
            $sql = "INSERT INTO $table ($columns) VALUES ($values) 
                     ON DUPLICATE KEY UPDATE $updateValuesString";
    
            $stmt = $this->pdo->prepare($sql);
    
            return $stmt->execute(array_merge(array_values($data), $updateParams));
    
        } else {
            $columns = implode(', ', array_keys(reset($data)));
            $values = '';
            $flatData = [];
    
            foreach ($data as $row) {
                $rowValues = implode(', ', array_fill(0, count($row), '?'));
    
                $values .= ($values ? ', ' : '') . "($rowValues)";
                $flatData = array_merge($flatData, array_values($row));
            }
    
            $updateValues = [];
            foreach (reset($data) as $column => $value) {
                $updateValues[] = $column . ' = VALUES(' . $column . ')';
            }
            $updateValuesString = implode(', ', $updateValues);
    
            $sql = "INSERT INTO $table ($columns) VALUES $values 
                     ON DUPLICATE KEY UPDATE $updateValuesString";
    
            $stmt = $this->pdo->prepare($sql);
    
            return $stmt->execute($flatData);
        }
    }

    public function update(string $table, array $data, string $condition): int
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';

        $sql = "UPDATE $table SET $set WHERE $condition";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(array_values($data));

        return (int) $stmt->rowCount();
    }

    public function delete(string $table, string $condition): int
    {
        $sql = "DELETE FROM $table WHERE $condition";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        return (int) $stmt->rowCount();
    }

    public function select(string $table, string $columns = '*', string $condition = '', array $params = [], int $limit = 0, string $order = ''): array
    {
        $sql = "SELECT $columns FROM $table";

        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }

        if ($order !== '') {
            $sql .= " ORDER BY $order";
        }

        if ($limit !== 0) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
