<?php
require_once __DIR__ . '/BaseModel.php';

class LeaveModel extends BaseModel {
    public function getAll(){
        // The actual table uses `from`, `to`, `reason` columns - map them to expected names
        $sql = 'SELECT l.id, l.employee_id, 
                l.`from` AS start_date, 
                l.`to` AS end_date, 
                COALESCE(l.reason, "pending") AS status, 
                e.name as employee_name 
                FROM leaves l 
                LEFT JOIN employees e ON e.id = l.employee_id 
                ORDER BY l.id DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function create($data){
        // Map incoming fields to actual table columns
        $stmt = $this->pdo->prepare('INSERT INTO leaves (employee_id, `from`, `to`, reason) VALUES (:e, :s, :t, :st)');
        $stmt->execute([
            ':e' => $data['employee_id'], 
            ':s' => $data['start_date'], 
            ':t' => $data['end_date'], 
            ':st' => $data['status'] ?? 'pending'
        ]);
        return $this->getById($this->pdo->lastInsertId());
    }
    
    public function getById($id){
        $sql = 'SELECT l.id, l.employee_id, 
                l.`from` AS start_date, 
                l.`to` AS end_date, 
                COALESCE(l.reason, "pending") AS status, 
                e.name as employee_name 
                FROM leaves l 
                LEFT JOIN employees e ON e.id = l.employee_id 
                WHERE l.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status){
        // Update the reason column (which stores status)
        $stmt = $this->pdo->prepare('UPDATE leaves SET reason = :status WHERE id = :id');
        $result = $stmt->execute([':id'=>$id, ':status'=>$status]);
        return $result && $stmt->rowCount() > 0;
    }
}
