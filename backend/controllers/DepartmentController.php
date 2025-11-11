<?php
/**
 * Department Controller
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/DepartmentModel.php';

class DepartmentController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new DepartmentModel();
    }

    public function handle() {
        $method = $this->getMethod();
        $id = $_GET['id'] ?? null;
        $this->requireAuth();
        
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
            $departments = $this->model->getAllWithDetails();
            $this->sendSuccess($departments);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $department = $this->model->getById($id);
            if (!$department) {
                $this->sendError('Department not found', 404);
            }
            $this->sendSuccess($department);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['name']);
            $data = $this->sanitize($data);
            $id = $this->model->create($data);
            $department = $this->model->getById($id);
            $this->sendSuccess($department, 'Department created successfully');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) {
                $this->sendError('Department not found', 404);
            }
            $data = $this->sanitize($data);
            $this->model->update($id, $data);
            $department = $this->model->getById($id);
            $this->sendSuccess($department, 'Department updated successfully');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) {
                $this->sendError('Department not found', 404);
            }
            $this->model->delete($id);
            $this->sendSuccess(null, 'Department deleted successfully');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
