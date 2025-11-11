<?php
/**
 * Attendance Controller
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/AttendanceModel.php';

class AttendanceController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new AttendanceModel();
    }

    public function handle() {
        $method = $this->getMethod();
        $id = $_GET['id'] ?? null;
        $action = $_GET['action'] ?? '';
        $this->requireAuth();
        
        if ($action === 'statistics') {
            $this->getStatistics();
            return;
        }
        
        if ($action === 'checkin' && $method === 'POST') {
            $this->checkIn();
            return;
        }
        
        if ($action === 'checkout' && $method === 'POST') {
            $this->checkOut();
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
            $date = $_GET['date'] ?? null;
            
            if ($date) {
                $attendance = $this->model->getByDate($date);
            } else {
                $attendance = $this->model->getAllWithDetails();
            }
            
            $this->sendSuccess($attendance);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $record = $this->model->getById($id);
            if (!$record) $this->sendError('Attendance not found', 404);
            $this->sendSuccess($record);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'attendance_date']);
            $data = $this->sanitize($data);
            $id = $this->model->create($data);
            $this->sendSuccess($this->model->getById($id), 'Attendance recorded');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function checkIn() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'date', 'check_in_time']);
            
            $id = $this->model->checkIn(
                $data['employee_id'],
                $data['date'],
                $data['check_in_time']
            );
            
            $this->sendSuccess($this->model->getById($id), 'Check-in successful');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function checkOut() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'date', 'check_out_time']);
            
            $success = $this->model->checkOut(
                $data['employee_id'],
                $data['date'],
                $data['check_out_time']
            );
            
            if ($success) {
                $this->sendSuccess(null, 'Check-out successful');
            } else {
                $this->sendError('Check-out failed');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) $this->sendError('Attendance not found', 404);
            $this->model->update($id, $this->sanitize($data));
            $this->sendSuccess($this->model->getById($id), 'Attendance updated');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) $this->sendError('Attendance not found', 404);
            $this->model->delete($id);
            $this->sendSuccess(null, 'Attendance deleted');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getStatistics() {
        try {
            $stats = $this->model->getStatistics();
            $this->sendSuccess($stats);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
