<?php
/**
 * Position Model
 */

require_once __DIR__ . '/Model.php';

class PositionModel extends Model {
    protected $table = 'positions';
    
    /**
     * Get all positions with department info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        p.*,
                        COUNT(e.id) as employee_count
                    FROM {$this->table} p
                    LEFT JOIN employees e ON e.position_id = p.id AND e.employment_status = 'active'
                    GROUP BY p.id
                    ORDER BY p.id";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching positions: " . $e->getMessage());
        }
    }

    /**
     * Get positions by department
     * @param int $departmentId
     * @return array
     */
    public function getByDepartment($departmentId) {
        try {
            return $this->getAll(['department_id' => $departmentId]);
        } catch (Exception $e) {
            throw new Exception("Error fetching positions: " . $e->getMessage());
        }
    }
}
