<?php
/**
 * Performance Controller
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/PerformanceModel.php';

class PerformanceController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = new PerformanceModel();
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
            $reviews = $this->model->getAllWithDetails();
            $this->sendSuccess($reviews);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getOne($id) {
        try {
            $review = $this->model->getById($id);
            if (!$review) $this->sendError('Review not found', 404);
            $this->sendSuccess($review);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function create() {
        try {
            $data = $this->getJsonInput();
            $this->validateRequired($data, ['employee_id', 'review_period_start', 'review_period_end', 'rating']);
            
            // Set reviewer_id from current user if not provided
            if (!isset($data['reviewer_id'])) {
                $user = $this->requireAuth();
                $data['reviewer_id'] = $user['id'];
            }
            
            // Set default status if not provided
            if (!isset($data['review_status'])) {
                $data['review_status'] = 'draft';
            }
            
            $data = $this->sanitize($data);
            
            $id = $this->model->create($data);
            $this->sendSuccess($this->model->getById($id), 'Review created');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function update($id) {
        try {
            $data = $this->getJsonInput();
            if (!$this->model->getById($id)) $this->sendError('Review not found', 404);
            $this->model->update($id, $this->sanitize($data));
            $this->sendSuccess($this->model->getById($id), 'Review updated');
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function delete($id) {
        try {
            if (!$this->model->getById($id)) $this->sendError('Review not found', 404);
            $this->model->delete($id);
            $this->sendSuccess(null, 'Review deleted');
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
