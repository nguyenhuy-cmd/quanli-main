<?php
/**
 * Salary Controller
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SalaryModel.php';

class SalaryController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new SalaryModel();
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
            $salaries = $this->model->getAllWithDetails();
            $this->sendSuccess($salaries);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $salary = $this->model->getById($id);
            if (!$salary) $this->sendError('Salary not found', 404);
            $this->sendSuccess($salary);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'base_salary', 'payment_date']);
            $data = $this->sanitize($data);
            $id = $this->model->create($data);
            $this->sendSuccess($this->model->getById($id), 'Salary created');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) $this->sendError('Salary not found', 404);
            $this->model->update($id, $this->sanitize($data));
            $this->sendSuccess($this->model->getById($id), 'Salary updated');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) $this->sendError('Salary not found', 404);
            $this->model->delete($id);
            $this->sendSuccess(null, 'Salary deleted');
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
