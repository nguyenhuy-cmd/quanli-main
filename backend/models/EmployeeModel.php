<?php
/**
 * Employee Model
 * Handles employee data management
 */

require_once __DIR__ . '/Model.php';

class EmployeeModel extends Model {
    protected $table = 'employees';
    
    /**
     * Get all employees with department and position info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        e.*,
                        d.name as department_name,
                        p.title as position_title,
                        p.base_salary as position_base_salary
                    FROM {$this->table} e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN positions p ON e.position_id = p.id
                    ORDER BY e.id DESC";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching employees: " . $e->getMessage());
        }
    }

    /**
     * Get employee by code
     * @param string $code
     * @return array|false
     */
    public function getByCode($code) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE employee_code = :code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':code', $code);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error fetching employee: " . $e->getMessage());
        }
    }

    /**
     * Search employees
     * @param string $keyword
     * @return array
     */
    public function searchEmployees($keyword) {
        try {
            $sql = "SELECT 
                        e.*,
                        d.name as department_name,
                        p.title as position_title
                    FROM {$this->table} e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN positions p ON e.position_id = p.id
                    WHERE e.full_name LIKE :keyword
                        OR e.employee_code LIKE :keyword
                        OR e.email LIKE :keyword
                        OR e.phone LIKE :keyword";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':keyword', "%$keyword%");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Search error: " . $e->getMessage());
        }
    }

    /**
     * Get employees by department
     * @param int $departmentId
     * @return array
     */
    public function getByDepartment($departmentId) {
        try {
            return $this->getAll(['department_id' => $departmentId]);
        } catch (Exception $e) {
            throw new Exception("Error fetching employees: " . $e->getMessage());
        }
    }

    /**
     * Get employees by status
     * @param string $status
     * @return array
     */
    public function getByStatus($status) {
        try {
            return $this->getAll(['employment_status' => $status]);
        } catch (Exception $e) {
            throw new Exception("Error fetching employees: " . $e->getMessage());
        }
    }

    /**
     * Generate unique employee code
     * @return string
     */
    public function generateEmployeeCode() {
        try {
            $sql = "SELECT employee_code FROM {$this->table} ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                $lastCode = $result['employee_code'];
                $number = intval(substr($lastCode, 3)) + 1;
                return 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
            
            return 'EMP001';
        } catch (PDOException $e) {
            throw new Exception("Error generating code: " . $e->getMessage());
        }
    }

    /**
     * Get employee statistics
     * @return array
     */
    public function getStatistics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_employees,
                        COUNT(CASE WHEN employment_status = 'active' THEN 1 END) as active_employees,
                        COUNT(CASE WHEN employment_status = 'on_leave' THEN 1 END) as on_leave_employees,
                        COUNT(CASE WHEN employment_status = 'terminated' THEN 1 END) as terminated_employees,
                        COUNT(CASE WHEN gender = 'male' THEN 1 END) as male_count,
                        COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_count
                    FROM {$this->table}";
            
            $result = $this->query($sql);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }
}
