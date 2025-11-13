<?php
/**
 * Leave Model
 */

require_once __DIR__ . '/Model.php';

class LeaveModel extends Model {
    protected $table = 'leaves';
    
    /**
     * Get all leaves with employee info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        l.*,
                        e.employee_code,
                        e.full_name,
                        d.name as department_name,
                        u.name as approved_by_name
                    FROM {$this->table} l
                    JOIN employees e ON l.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN users u ON l.approved_by = u.id
                    ORDER BY l.created_at DESC";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching leaves: " . $e->getMessage());
        }
    }

    /**
     * Get leaves by employee
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee($employeeId) {
        try {
            return $this->getAll(['employee_id' => $employeeId], 'created_at DESC');
        } catch (Exception $e) {
            throw new Exception("Error fetching leaves: " . $e->getMessage());
        }
    }

    /**
     * Get leaves by status
     * @param string $status
     * @return array
     */
    public function getByStatus($status) {
        try {
            $sql = "SELECT 
                        l.*,
                        e.employee_code,
                        e.full_name,
                        d.name as department_name
                    FROM {$this->table} l
                    JOIN employees e ON l.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    WHERE l.status = :status
                    ORDER BY l.created_at DESC";
            
            return $this->query($sql, [':status' => $status]);
        } catch (Exception $e) {
            throw new Exception("Error fetching leaves: " . $e->getMessage());
        }
    }

    /**
     * Approve leave
     * @param int $leaveId
     * @param int $approverId
     * @return bool
     */
    public function approve($leaveId, $approverId) {
        try {
            return $this->update($leaveId, [
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            throw new Exception("Approval failed: " . $e->getMessage());
        }
    }

    /**
     * Reject leave
     * @param int $leaveId
     * @param int $approverId
     * @param string $note
     * @return bool
     */
    public function reject($leaveId, $approverId, $note = '') {
        try {
            $data = [
                'status' => 'rejected',
                'approved_by' => $approverId,
                'approved_at' => date('Y-m-d H:i:s')
            ];
            
            if ($note) {
                $data['notes'] = $note;
            }
            
            return $this->update($leaveId, $data);
        } catch (Exception $e) {
            throw new Exception("Rejection failed: " . $e->getMessage());
        }
    }

    /**
     * Get leave statistics
     * @param int $employeeId
     * @param int $year
     * @return array
     */
    public function getStatistics($employeeId = null, $year = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_requests,
                        SUM(days) as total_days,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
                        COUNT(CASE WHEN leave_type = 'annual' THEN 1 END) as annual_count,
                        COUNT(CASE WHEN leave_type = 'sick' THEN 1 END) as sick_count
                    FROM {$this->table}
                    WHERE 1=1";
            
            $params = [];
            
            if ($employeeId) {
                $sql .= " AND employee_id = :employee_id";
                $params[':employee_id'] = $employeeId;
            }
            
            if ($year) {
                $sql .= " AND YEAR(start_date) = :year";
                $params[':year'] = $year;
            }
            
            $result = $this->query($sql, $params);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }

    /**
     * Calculate leave days
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function calculateDays($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = $start->diff($end);
        return $interval->days + 1; // Include both start and end dates
    }
}
