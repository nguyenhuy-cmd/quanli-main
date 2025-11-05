<?php
require_once __DIR__ . '/../models/LeaveModel.php';
require_once __DIR__ . '/BaseController.php';

class LeaveController extends BaseController {
    private $model;
    public function __construct(){
        $this->model = new LeaveModel();
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
                    if(empty($body['start_date'])) $details['start_date'] = 'Ngày bắt đầu là bắt buộc';
                    if(empty($body['end_date'])) $details['end_date'] = 'Ngày kết thúc là bắt buộc';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại',400,$details);
                    // verify employee exists
                    $pdo = getPDO();
                    $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $body['employee_id']]);
                    $exists = $stmt->fetch();
                    if(!$exists){
                        $this->jsonError('Nhân viên không tồn tại', 400, ['employee_id'=>'Nhân viên không tồn tại']);
                    }
                    $created = $this->model->create($body);
                    $this->jsonSuccess($created,201);
                    break;
                case 'PUT':
                    // Update leave status
                    $details = [];
                    if(empty($body['id'])) $details['id'] = 'ID yêu cầu nghỉ phép là bắt buộc';
                    if(empty($body['status'])) $details['status'] = 'Trạng thái là bắt buộc';
                    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại',400,$details);
                    
                    // Validate status value
                    $allowedStatuses = ['pending', 'approved', 'rejected'];
                    if(!in_array($body['status'], $allowedStatuses)){
                        $this->jsonError('Trạng thái không hợp lệ', 400, ['status'=>'Trạng thái phải là: pending, approved, hoặc rejected']);
                    }
                    
                    $updated = $this->model->updateStatus($body['id'], $body['status']);
                    if(!$updated){
                        $this->jsonError('Không tìm thấy yêu cầu nghỉ phép hoặc cập nhật thất bại', 404);
                    }
                    $this->jsonSuccess(['success'=>true, 'message'=>'Cập nhật trạng thái thành công']);
                    break;
                default:
                    $this->jsonError('Chức năng chưa được triển khai',501);
            }
        }catch(Exception $e){
            $this->jsonError($e->getMessage(),500);
        }
    }
}
