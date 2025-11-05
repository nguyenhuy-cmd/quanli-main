<?php
require_once __DIR__ . '/BaseModel.php';

class AttendanceModel extends BaseModel {
    public function getAll(){
        $stmt = $this->pdo->query('SELECT * FROM attendance ORDER BY date DESC');
        return $stmt->fetchAll();
    }
    public function create($data){
        $stmt = $this->pdo->prepare('INSERT INTO attendance (employee_id,date,status) VALUES (:e,:d,:s)');
        $stmt->execute([':e'=>$data['employee_id'], ':d'=>$data['date'], ':s'=>$data['status']]);
        return $this->getById($this->pdo->lastInsertId());
    }
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT * FROM attendance WHERE id=:id');
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
}
