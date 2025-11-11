<?php
/**
 * Leave Controller
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/LeaveModel.php';

class LeaveController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new LeaveModel();
    }

    public function handle() {
        $method = $this->getMethod();
        $id = $_GET['id'] ?? null;
        $action = $_GET['action'] ?? '';
        $this->requireAuth();
        
        if ($action === 'approve' && $id) {
            $this->approve($id);
            return;
        }
        
        if ($action === 'reject' && $id) {
            $this->reject($id);
            return;
        }
        
        switch ($method) {
            case 'GET':
                $id ? $this->getOne($id) : $this->getAll();
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                $id ? $this->update($id) : $this->sendError('ID required');
                break;
            case 'DELETE':
                $id ? $this->delete($id) : $this->sendError('ID required');
                break;
        }
    }

    private function getAll() {
        try {
            $leaves = $this->model->getAllWithDetails();
            $this->sendSuccess($leaves);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $leave = $this->model->getById($id);
            if (!$leave) $this->sendError('Leave not found', 404);
            $this->sendSuccess($leave);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'leave_type', 'start_date', 'end_date', 'reason']);
            
            // Calculate days if not provided
            if (!isset($data['total_days'])) {
                $data['total_days'] = $this->model->calculateDays($data['start_date'], $data['end_date']);
            }
            
            // Set default status if not provided
            if (!isset($data['leave_status'])) {
                $data['leave_status'] = 'pending';
            }
            
            $data = $this->sanitize($data);
            
            $id = $this->model->create($data);
            $this->sendSuccess($this->model->getById($id), 'Leave request created');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) $this->sendError('Leave not found', 404);
            $this->model->update($id, $this->sanitize($data));
            $this->sendSuccess($this->model->getById($id), 'Leave updated');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) $this->sendError('Leave not found', 404);
            $this->model->delete($id);
            $this->sendSuccess(null, 'Leave deleted');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function approve($id) {
        try {
            $user = $this->requireAuth();
            $this->model->approve($id, $user['id']);
            $this->sendSuccess($this->model->getById($id), 'Leave approved');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function reject($id) {
        try {
            $user = $this->requireAuth();
            $data = $this->getJsonInput();
            $note = $data['note'] ?? '';
            $this->model->reject($id, $user['id'], $note);
            $this->sendSuccess($this->model->getById($id), 'Leave rejected');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
