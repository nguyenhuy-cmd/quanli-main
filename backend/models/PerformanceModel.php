<?php
/**
 * Performance Review Model
 */

require_once __DIR__ . '/Model.php';

class PerformanceModel extends Model {
    protected $table = 'performance_reviews';
    
    /**
     * Get all reviews with employee and reviewer info
     * @return array
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT 
                        pr.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        d.name as department_name,
                        u.name as reviewer_name,
                        CONCAT(DATE_FORMAT(pr.period_start, '%m/%Y'), ' - ', DATE_FORMAT(pr.period_end, '%m/%Y')) as review_period
                    FROM {$this->table} pr
                    JOIN employees e ON pr.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN users u ON pr.reviewer_id = u.id
                    ORDER BY pr.created_at DESC";
            
            return $this->query($sql);
        } catch (Exception $e) {
            throw new Exception("Error fetching reviews: " . $e->getMessage());
        }
    }

    /**
     * Get reviews by employee
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee($employeeId) {
        try {
            $sql = "SELECT 
                        pr.*,
                        u.name as reviewer_name,
                        CONCAT(DATE_FORMAT(pr.period_start, '%m/%Y'), ' - ', DATE_FORMAT(pr.period_end, '%m/%Y')) as review_period
                    FROM {$this->table} pr
                    LEFT JOIN users u ON pr.reviewer_id = u.id
                    WHERE pr.employee_id = :employee_id
                    ORDER BY pr.created_at DESC";
            
            return $this->query($sql, [':employee_id' => $employeeId]);
        } catch (Exception $e) {
            throw new Exception("Error fetching reviews: " . $e->getMessage());
        }
    }

    /**
     * Get average rating by employee
     * @param int $employeeId
     * @return float
     */
    public function getAverageRating($employeeId) {
        try {
            $sql = "SELECT AVG(rating) as avg_rating 
                    FROM {$this->table} 
                    WHERE employee_id = :employee_id AND status = 'completed'";
            
            $result = $this->query($sql, [':employee_id' => $employeeId]);
            return $result[0]['avg_rating'] ?? 0;
        } catch (Exception $e) {
            throw new Exception("Error calculating rating: " . $e->getMessage());
        }
    }

    /**
     * Get performance statistics
     * @return array
     */
    public function getStatistics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(rating) as overall_avg_rating,
                        COUNT(CASE WHEN rating >= 4.5 THEN 1 END) as excellent_count,
                        COUNT(CASE WHEN rating >= 3.5 AND rating < 4.5 THEN 1 END) as good_count,
                        COUNT(CASE WHEN rating >= 2.5 AND rating < 3.5 THEN 1 END) as average_count,
                        COUNT(CASE WHEN rating >= 1.5 AND rating < 2.5 THEN 1 END) as below_avg_count,
                        COUNT(CASE WHEN rating < 1.5 THEN 1 END) as poor_count,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_count
                    FROM {$this->table}";
            
            $result = $this->query($sql);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }

    /**
     * Get top performers
     * @param int $limit
     * @return array
     */
    public function getTopPerformers($limit = 10) {
        try {
            $sql = "SELECT 
                        e.employee_code,
                        e.full_name,
                        d.name as department_name,
                        AVG(pr.rating) as avg_rating,
                        COUNT(pr.id) as review_count
                    FROM employees e
                    JOIN {$this->table} pr ON e.id = pr.employee_id
                    LEFT JOIN departments d ON e.department_id = d.id
                    WHERE pr.status = 'completed'
                    GROUP BY e.id
                    HAVING COUNT(pr.id) > 0
                    ORDER BY avg_rating DESC, review_count DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error fetching top performers: " . $e->getMessage());
        }
    }

    /**
     * Get reviews by department
     * @param int $departmentId
     * @return array
     */
    public function getByDepartment($departmentId) {
        try {
            $sql = "SELECT 
                        pr.*,
                        e.employee_code,
                        e.full_name as employee_name,
                        CONCAT(DATE_FORMAT(pr.review_period_start, '%m/%Y'), ' - ', DATE_FORMAT(pr.review_period_end, '%m/%Y')) as review_period
                    FROM {$this->table} pr
                    JOIN employees e ON pr.employee_id = e.id
                    WHERE e.department_id = :department_id
                    ORDER BY pr.created_at DESC";
            
            return $this->query($sql, [':department_id' => $departmentId]);
        } catch (Exception $e) {
            throw new Exception("Error fetching reviews: " . $e->getMessage());
        }
    }
}
