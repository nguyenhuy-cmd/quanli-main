<?php
/**
 * User Model
 * Handles authentication and user management
 */

require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    protected $table = 'users';
    
    /**
     * Find user by email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error finding user: " . $e->getMessage());
        }
    }

    /**
     * Register new user
     * @param array $data
     * @return int User ID
     */
    public function register($data) {
        try {
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Set default role if not provided
            if (!isset($data['role'])) {
                $data['role'] = 'employee';
            }
            
            return $this->create($data);
        } catch (Exception $e) {
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }

    /**
     * Verify user credentials
     * @param string $email
     * @param string $password
     * @return array|false User data or false
     */
    public function verifyCredentials($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user) {
                return false;
            }
            
            if (password_verify($password, $user['password'])) {
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception("Verification failed: " . $e->getMessage());
        }
    }

    /**
     * Update password
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->update($userId, ['password' => $hashedPassword]);
        } catch (Exception $e) {
            throw new Exception("Password update failed: " . $e->getMessage());
        }
    }

    /**
     * Get users by role
     * @param string $role
     * @return array
     */
    public function getUsersByRole($role) {
        try {
            return $this->getAll(['role' => $role]);
        } catch (Exception $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }
}
