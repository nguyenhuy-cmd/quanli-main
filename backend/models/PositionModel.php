<?php
require_once __DIR__ . '/BaseModel.php';

class PositionModel extends BaseModel {
    public function getAll(){
        $stmt = $this->pdo->query("SELECT * FROM positions ORDER BY id");
        return $stmt->fetchAll();
    }
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT * FROM positions WHERE id=:id');
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
    public function create($data){
        $stmt = $this->pdo->prepare('INSERT INTO positions (name) VALUES (:name)');
        $stmt->execute([':name'=>$data['name']]);
        return $this->getById($this->pdo->lastInsertId());
    }
    public function update($data){
        $stmt = $this->pdo->prepare('UPDATE positions SET name=:name WHERE id=:id');
        $stmt->execute([':name'=>$data['name'], ':id'=>$data['id']]);
        return $this->getById($data['id']);
    }
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM positions WHERE id=:id');
        return $stmt->execute([':id'=>$id]);
    }
}
