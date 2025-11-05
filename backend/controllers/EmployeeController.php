<?php
require_once __DIR__ . '/../models/EmployeeModel.php';
require_once __DIR__ . '/BaseController.php';

class EmployeeController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new EmployeeModel();
    }
    public function handle($method, $id=null, $body=null){
        try{
            switch($method){
                case 'GET':
                    if($id) $this->jsonSuccess($this->model->getById($id));
                    if(isset($_GET['search'])) $this->jsonSuccess($this->model->getAll($_GET['search']));
                    $this->jsonSuccess($this->model->getAll());
                    break;
                case 'POST':
                    // basic validation
                    $details = [];
                    if(empty($body['name'])) $details['name'] = 'Tên là bắt buộc';
                    if(!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) $details['email'] = 'Email không hợp lệ';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại', 400, $details);
                    $created = $this->model->create($body);
                    $this->jsonSuccess($created,201);
                    break;
                case 'PUT':
                    $details = [];
                    if(empty($body['id'])) $details['id'] = 'ID là bắt buộc';
                    if(empty($body['name'])) $details['name'] = 'Tên là bắt buộc';
                    if(!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) $details['email'] = 'Email không hợp lệ';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại', 400, $details);
                    $updated = $this->model->update($body);
                    $this->jsonSuccess($updated);
                    break;
                case 'DELETE':
                    $ok = $this->model->delete($body['id'] ?? null);
                    $this->jsonSuccess(['success'=>!!$ok]);
                    break;
                default:
                    $this->jsonError('Phương thức không được phép',405);
            }
        }catch(Exception $e){
            $this->jsonError($e->getMessage(), 500);
        }
    }
}
