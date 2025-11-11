<?php
/**
 * Position Controller - Similar pattern to DepartmentController
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/PositionModel.php';

class PositionController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new PositionModel();
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
            $positions = $this->model->getAllWithDetails();
            $this->sendSuccess($positions);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $position = $this->model->getById($id);
            if (!$position) $this->sendError('Position not found', 404);
            $this->sendSuccess($position);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['title']);
            $data = $this->sanitize($data);
            $id = $this->model->create($data);
            $this->sendSuccess($this->model->getById($id), 'Position created');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) $this->sendError('Position not found', 404);
            $this->model->update($id, $this->sanitize($data));
            $this->sendSuccess($this->model->getById($id), 'Position updated');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) $this->sendError('Position not found', 404);
            $this->model->delete($id);
            $this->sendSuccess(null, 'Position deleted');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
