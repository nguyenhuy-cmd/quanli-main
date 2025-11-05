<?php
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/BaseController.php';

class ReviewController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new ReviewModel();
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
                    if(!isset($body['score']) || !is_numeric($body['score'])) $details['score'] = 'Điểm là bắt buộc và phải là số';
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
