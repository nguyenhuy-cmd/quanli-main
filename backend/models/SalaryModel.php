<?php
require_once __DIR__ . '/BaseModel.php';

class SalaryModel extends BaseModel {
    public function getAll(){
        // include employee name for display
        $stmt = $this->pdo->query('SELECT s.*, e.name as employee_name FROM salaries s LEFT JOIN employees e ON e.id = s.employee_id ORDER BY s.id DESC');
        return $stmt->fetchAll();
    }
    public function create($data){
        // Accept 'month' in format YYYY-MM or 'pay_date' (YYYY-MM-DD). Store as pay_date in DB.
        $payDate = null;
        if(!empty($data['pay_date'])){
            $payDate = $data['pay_date'];
        }elseif(!empty($data['month'])){
            // convert YYYY-MM to first day of month
            $payDate = preg_match('/^\d{4}-\d{2}$/', $data['month']) ? ($data['month'] . '-01') : null;
        }
        $stmt = $this->pdo->prepare('INSERT INTO salaries (employee_id,amount,pay_date) VALUES (:e,:a,:p)');
        $stmt->execute([':e'=>$data['employee_id'], ':a'=>$data['amount'], ':p'=>$payDate]);
        return $this->getById($this->pdo->lastInsertId());
    }
    public function getById($id){
        $stmt = $this->pdo->prepare('SELECT s.*, e.name as employee_name FROM salaries s LEFT JOIN employees e ON e.id = s.employee_id WHERE s.id=:id');
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
}
