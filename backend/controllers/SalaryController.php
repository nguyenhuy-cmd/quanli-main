<?php
require_once __DIR__ . '/../models/SalaryModel.php';
require_once __DIR__ . '/BaseController.php';

class SalaryController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new SalaryModel();
    }
    public function handle($method, $id=null, $body=null){
        try{
            switch($method){
                case 'GET':
                    $this->jsonSuccess($this->model->getAll());
                    break;
                case 'POST':
                    $details = [];
                    if(empty($body['employee_id'])) $details['employee_id'] = 'Mã nhân viên là bắt buộc';
                    if(!isset($body['amount']) || !is_numeric($body['amount']) ) $details['amount'] = 'Số tiền là bắt buộc và phải là số';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại',400,$details);
                    // verify employee exists to avoid foreign key errors
                    $pdo = getPDO();
                    $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $body['employee_id']]);
                    $exists = $stmt->fetch();
                    if(!$exists){
                        $this->jsonError('Nhân viên không tồn tại', 400, ['employee_id'=>'Nhân viên không tồn tại']);
                    }
                    // normalize month -> pay_date if provided
                    if(!empty($body['month']) && empty($body['pay_date'])){
                        if(preg_match('/^\d{4}-\d{2}$/', $body['month'])){
                            $body['pay_date'] = $body['month'] . '-01';
                        }else{
                            // leave as-is; model will handle null
                        }
                    }
                    $created = $this->model->create($body);
                    $this->jsonSuccess($created,201);
                    break;
                default:
                    $this->jsonError('Chức năng chưa được triển khai',501);
            }
        }catch(Exception $e){
            $this->jsonError($e->getMessage(),500);
        }
    }
}
