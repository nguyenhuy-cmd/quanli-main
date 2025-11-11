<?php
/**
 * Base Model Class
 * Implements basic CRUD operations using OOP
 */

require_once __DIR__ . '/../config/config.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all records
     * @param array $conditions WHERE conditions
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT
     * @param int $offset OFFSET
     * @return array
     */
    public function getAll($conditions = [], $orderBy = null, $limit = null, $offset = null) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            if ($limit) {
                $sql .= " LIMIT $limit";
                if ($offset) {
                    $sql .= " OFFSET $offset";
                }
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($conditions)) {
                foreach ($conditions as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error fetching records: " . $e->getMessage());
        }
    }

    /**
     * Get single record by ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error fetching record: " . $e->getMessage());
        }
    }

    /**
     * Insert new record
     * @param array $data
     * @return int Last insert ID
     */
    public function create($data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            
            $fieldsList = implode(', ', $fields);
            $placeholders = ':' . implode(', :', $fields);
            
            $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error creating record: " . $e->getMessage());
        }
    }

    /**
     * Update record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error updating record: " . $e->getMessage());
        }
    }

    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error deleting record: " . $e->getMessage());
        }
    }

    /**
     * Count records
     * @param array $conditions
     * @return int
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($conditions)) {
                foreach ($conditions as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)$result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error counting records: " . $e->getMessage());
        }
    }

    /**
     * Search records
     * @param array $searchFields Fields to search
     * @param string $keyword Search keyword
     * @return array
     */
    public function search($searchFields, $keyword) {
        try {
            $conditions = [];
            foreach ($searchFields as $field) {
                $conditions[] = "$field LIKE :keyword";
            }
            
            $sql = "SELECT * FROM {$this->table} WHERE " . implode(' OR ', $conditions);
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':keyword', "%$keyword%");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error searching records: " . $e->getMessage());
        }
    }

    /**
     * Execute custom query
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query error: " . $e->getMessage());
        }
    }

    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    protected function commit() {
        $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    protected function rollback() {
        $this->db->rollBack();
    }
}
