<?php
/**
 * Employee Controller
 * Handles employee CRUD operations
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/EmployeeModel.php';

class EmployeeController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new EmployeeModel();
    }

    /**
     * Handle employee requests
     */
    public function handle() {
        $method = $this->getMethod();
        $id = $_GET['id'] ?? null;
        $action = $_GET['action'] ?? '';
        
        // Require authentication for all employee operations
        $this->requireAuth();
        
        switch ($method) {
            case 'GET':
                if ($action === 'search') {
                    $this->search();
                } elseif ($action === 'statistics') {
                    $this->getStatistics();
                } elseif ($id) {
                    $this->getOne($id);
                } else {
                    $this->getAll();
                }
                break;
            
            case 'POST':
                $this->create();
                break;
            
            case 'PUT':
                if ($id) {
                    $this->update($id);
                } else {
                    $this->sendError('ID is required for update');
                }
                break;
            
            case 'DELETE':
                if ($id) {
                    $this->delete($id);
                } else {
                    $this->sendError('ID is required for delete');
                }
                break;
            
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Get all employees
     */
    private function getAll() {
        try {
            $employees = $this->model->getAllWithDetails();
            $this->sendSuccess($employees);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get single employee
     */
    private function getOne($id) {
        try {
            $employee = $this->model->getById($id);
            if (!$employee) {
                $this->sendError('Employee not found', 404);
            }
            $this->sendSuccess($employee);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Create new employee
     */
    private function create() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['full_name', 'email', 'hire_date']);
            
            // Validate email
            if (!$this->validateEmail($data['email'])) {
                $this->sendError('Invalid email format');
            }
            
            // Generate employee code if not provided
            if (empty($data['employee_code'])) {
                $data['employee_code'] = $this->model->generateEmployeeCode();
            }
            
            // Sanitize input
            $data = $this->sanitize($data);
            
            // Create employee
            $id = $this->model->create($data);
            $employee = $this->model->getById($id);
            
            $this->sendSuccess($employee, 'Employee created successfully');
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Update employee
     */
    private function update($id) {
        try {
            $data = $this->getJsonInput();
            
            // Check if employee exists
            $employee = $this->model->getById($id);
            if (!$employee) {
                $this->sendError('Employee not found', 404);
            }
            
            // Validate email if provided
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $this->sendError('Invalid email format');
            }
            
            // Sanitize input
            $data = $this->sanitize($data);
            
            // Update employee
            $this->model->update($id, $data);
            $updatedEmployee = $this->model->getById($id);
            
            $this->sendSuccess($updatedEmployee, 'Employee updated successfully');
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Delete employee
     */
    private function delete($id) {
        try {
            // Check if employee exists
            $employee = $this->model->getById($id);
            if (!$employee) {
                $this->sendError('Employee not found', 404);
            }
            
            // Delete employee
            $this->model->delete($id);
            
            $this->sendSuccess(null, 'Employee deleted successfully');
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Search employees
     */
    private function search() {
        try {
            $keyword = $_GET['keyword'] ?? '';
            
            if (empty($keyword)) {
                $this->sendError('Search keyword is required');
            }
            
            $employees = $this->model->searchEmployees($keyword);
            $this->sendSuccess($employees);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get employee statistics
     */
    private function getStatistics() {
        try {
            $stats = $this->model->getStatistics();
            $this->sendSuccess($stats);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
