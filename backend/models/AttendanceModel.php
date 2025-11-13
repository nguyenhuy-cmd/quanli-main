<?php
/**
 * Attendance Model
 */

require_once __DIR__ . '/Model.php';

class AttendanceModel extends Model {
    protected $table = 'attendance';
    
    /**
     * Get all attendance with employee info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        a.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        d.name as department_name
                    FROM {$this->table} a
                    JOIN employees e ON a.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    ORDER BY a.date DESC, a.id DESC";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching attendance: " . $e->getMessage());
        }
    }

    /**
     * Get attendance by employee
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByEmployee($employeeId, $startDate = null, $endDate = null) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE employee_id = :employee_id";
            $params = [':employee_id' => $employeeId];
            
            if ($startDate && $endDate) {
                $sql .= " AND date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $sql .= " ORDER BY date DESC";
            
            return $this->query($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error fetching attendance: " . $e->getMessage());
        }
    }

    /**
     * Get attendance by date
     * @param string $date
     * @return array
     */
    public function getByDate($date) {
        try {
            $sql = "SELECT 
                        a.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        d.name as department_name
                    FROM {$this->table} a
                    JOIN employees e ON a.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    WHERE a.date = :date
                    ORDER BY e.full_name";
            
            return $this->query($sql, [':date' => $date]);
        } catch (Exception $e) {
            throw new Exception("Error fetching attendance: " . $e->getMessage());
        }
    }

    /**
     * Check in
     * @param int $employeeId
     * @param string $date
     * @param string $time
     * @return int
     */
    public function checkIn($employeeId, $date, $time) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'date' => $date,
                'check_in' => $time,
                'status' => $time > '08:30:00' ? 'late' : 'present'
            ];
            
            return $this->create($data);
        } catch (Exception $e) {
            throw new Exception("Check-in failed: " . $e->getMessage());
        }
    }

    /**
     * Check out
     * @param int $employeeId
     * @param string $date
     * @param string $time
     * @return bool
     */
    public function checkOut($employeeId, $date, $time) {
        try {
            $sql = "SELECT id, check_in FROM {$this->table} 
                    WHERE employee_id = :employee_id AND date = :date LIMIT 1";
            $record = $this->query($sql, [
                ':employee_id' => $employeeId,
                ':date' => $date
            ]);
            
            if (empty($record)) {
                throw new Exception("No check-in record found");
            }
            
            $checkIn = strtotime($record[0]['check_in']);
            $checkOut = strtotime($time);
            $workHours = ($checkOut - $checkIn) / 3600;
            
            return $this->update($record[0]['id'], [
                'check_out' => $time,
                'work_hours' => round($workHours, 2)
            ]);
        } catch (Exception $e) {
            throw new Exception("Check-out failed: " . $e->getMessage());
        }
    }

    /**
     * Get attendance statistics
     * @param int $employeeId
     * @param string $month Format: YYYY-MM
     * @return array
     */
    public function getStatistics($employeeId = null, $month = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_days,
                        SUM(work_hours) as total_hours,
                        COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                        COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days,
                        COUNT(CASE WHEN status = 'early_leave' THEN 1 END) as early_leave_days
                    FROM {$this->table}
                    WHERE 1=1";
            
            $params = [];
            
            if ($employeeId) {
                $sql .= " AND employee_id = :employee_id";
                $params[':employee_id'] = $employeeId;
            }
            
            if ($month) {
                $sql .= " AND DATE_FORMAT(date, '%Y-%m') = :month";
                $params[':month'] = $month;
            }
            
            $result = $this->query($sql, $params);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }
}
