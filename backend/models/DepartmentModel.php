<?php
/**
 * Department Model
 */

require_once __DIR__ . '/Model.php';

class DepartmentModel extends Model {
    protected $table = 'departments';
    
    /**
     * Get all departments with manager info and employee count
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        d.*,
                        e.full_name as manager_name,
                        COUNT(emp.id) as employee_count
                    FROM {$this->table} d
                    LEFT JOIN employees e ON d.manager_id = e.id
                    LEFT JOIN employees emp ON emp.department_id = d.id
                    GROUP BY d.id
                    ORDER BY d.id";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching departments: " . $e->getMessage());
        }
    }

    /**
     * Get department statistics
     * @return array
     */
    public function getStatistics() {
        try {
            $sql = "SELECT 
                        d.id,
                        d.name,
                        COUNT(e.id) as employee_count,
                        AVG(p.base_salary) as avg_salary
                    FROM {$this->table} d
                    LEFT JOIN employees e ON e.department_id = d.id AND e.status = 'active'
                    LEFT JOIN positions p ON e.position_id = p.id
                    GROUP BY d.id, d.name";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }
}
