<?php
require_once __DIR__ . '/BaseModel.php';

class ReviewModel extends BaseModel {
    public function getAll(){
        $stmt = $this->pdo->query('SELECT * FROM reviews ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
    public function create($data){
        $stmt = $this->pdo->prepare('INSERT INTO reviews (employee_id,score,note) VALUES (:e,:s,:n)');
        $stmt->execute([':e'=>$data['employee_id'], ':s'=>$data['score'], ':n'=>$data['note']]);
        return $this->getById($this->pdo->lastInsertId());
    }
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT * FROM reviews WHERE id=:id');
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
}
