<?php
/**
 * Salary Model
 */

require_once __DIR__ . '/Model.php';

class SalaryModel extends Model {
    protected $table = 'salaries';
    
    /**
     * Get all salaries with employee info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        s.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        d.name as department_name,
                        MONTH(s.salary_month) as month,
                        YEAR(s.salary_month) as year
                    FROM {$this->table} s
                    JOIN employees e ON s.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    ORDER BY s.payment_date DESC, s.id DESC";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching salaries: " . $e->getMessage());
        }
    }

    /**
     * Get salaries by employee
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee($employeeId) {
        try {
            return $this->getAll(['employee_id' => $employeeId], 'payment_date DESC');
        } catch (Exception $e) {
            throw new Exception("Error fetching salaries: " . $e->getMessage());
        }
    }

    /**
     * Get salaries by month
     * @param string $month Format: YYYY-MM
     * @return array
     */
    public function getByMonth($month) {
        try {
            $sql = "SELECT 
                        s.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        d.name as department_name,
                        MONTH(s.salary_month) as month,
                        YEAR(s.salary_month) as year
                    FROM {$this->table} s
                    JOIN employees e ON s.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    WHERE DATE_FORMAT(s.salary_month, '%Y-%m') = :month
                    ORDER BY s.id";
            
            return $this->query($sql, [':month' => $month]);
        } catch (Exception $e) {
            throw new Exception("Error fetching salaries: " . $e->getMessage());
        }
    }

    /**
     * Get salary statistics
     * @return array
     */
    public function getStatistics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        SUM(total_salary) as total_amount,
                        AVG(total_salary) as avg_salary,
                        MAX(total_salary) as max_salary,
                        MIN(total_salary) as min_salary,
                        COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_count,
                        COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_count
                    FROM {$this->table}";
            
            $result = $this->query($sql);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }

    /**
     * Calculate and create salary for employee
     * @param array $data
     * @return int
     */
    public function calculateAndCreate($data) {
        try {
            // Total salary will be calculated by MySQL (generated column)
            return $this->create($data);
        } catch (Exception $e) {
            throw new Exception("Error creating salary: " . $e->getMessage());
        }
    }
}
