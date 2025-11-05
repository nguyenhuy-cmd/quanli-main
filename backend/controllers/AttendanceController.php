<?php
require_once __DIR__ . '/../models/AttendanceModel.php';
require_once __DIR__ . '/BaseController.php';

class AttendanceController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new AttendanceModel();
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
                    if(empty($body['date'])) $details['date'] = 'Ngày là bắt buộc';
                    if(empty($body['status'])) $details['status'] = 'Trạng thái là bắt buộc';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại',400,$details);
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
