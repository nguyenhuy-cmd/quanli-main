<?php
require_once __DIR__ . '/../models/DepartmentModel.php';
require_once __DIR__ . '/BaseController.php';

class DepartmentController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new DepartmentModel();
    }
    public function handle($method, $id=null, $body=null){
        try{
            switch($method){
                case 'GET':
                    if($id) $this->jsonSuccess($this->model->getById($id));
                    $this->jsonSuccess($this->model->getAll());
                    break;
                case 'POST':
                    $details = [];
                    if(empty($body['name'])) $details['name'] = 'Tên là bắt buộc';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại', 400, $details);
                    $created = $this->model->create($body);
                    $this->jsonSuccess($created,201);
                    break;
                case 'PUT':
                    $details = [];
                    if(empty($body['id'])) $details['id'] = 'ID là bắt buộc';
                    if(empty($body['name'])) $details['name'] = 'Tên là bắt buộc';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại',400,$details);
                    $updated = $this->model->update($body);
                    $this->jsonSuccess($updated);
                    break;
                case 'DELETE':
                    if(empty($body['id'])) $this->jsonError('Thiếu ID',400,['id'=>'ID là bắt buộc']);
                    $ok = $this->model->delete($body['id']);
                    $this->jsonSuccess(['success'=>!!$ok]);
                    break;
                default:
                    $this->jsonError('Method not allowed',405);
            }
        }catch(Exception $e){
            $this->jsonError($e->getMessage(),500);
        }
    }
}
