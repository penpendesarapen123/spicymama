<?php
require_once __DIR__.'/../_init.php';

class UserLog
{
    public static function getUserLogs($userId, $role)
    {
        global $connection;
        $query = "SELECT users.name AS username, user_logs.role, user_logs.login_time AS loginTime, user_logs.logout_time AS logoutTime 
                  FROM user_logs 
                  INNER JOIN users ON user_logs.user_id = users.id";

        // Filter logs based on role
        if ($role === 'CASHIER') {
            $query .= " WHERE user_logs.user_id = :user_id";
            $params = [':user_id' => $userId];
        } elseif ($role === 'MANAGER') {
            $query .= " WHERE users.role IN ('CASHIER', 'MANAGER')";
            $params = [];
        } else { // admin
            $params = [];
        }
        
        $query .= " ORDER BY user_logs.login_time DESC";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>