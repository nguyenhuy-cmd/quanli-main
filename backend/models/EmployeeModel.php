<?php
require_once __DIR__ . '/BaseModel.php';

class EmployeeModel extends BaseModel {
    public function getAll($search=null){
        if($search){
            $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE name LIKE :q OR email LIKE :q ORDER BY id DESC");
            $stmt->execute([':q'=>'%'.$search.'%']);
        }else{
            $stmt = $this->pdo->query("SELECT * FROM employees ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }
    public function getById($id){
        $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
    public function create($data){
        $stmt = $this->pdo->prepare("INSERT INTO employees (name,email,department_id,position_id) VALUES (:name,:email,:dep,:pos)");
        $stmt->execute([':name'=>$data['name'],':email'=>$data['email'],':dep'=>$data['department_id']??null,':pos'=>$data['position_id']??null]);
        return $this->getById($this->pdo->lastInsertId());
    }
    public function update($data){
        $stmt = $this->pdo->prepare("UPDATE employees SET name=:name,email=:email,department_id=:dep,position_id=:pos WHERE id=:id");
        $stmt->execute([':name'=>$data['name'],':email'=>$data['email'],':dep'=>$data['department_id']??null,':pos'=>$data['position_id']??null,':id'=>$data['id']]);
        return $this->getById($data['id']);
    }
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM employees WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }
}
