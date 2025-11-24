<?php

require_once __DIR__.'/../_init.php';

class User
{
    public $id;
    public $name;
    public $email;
    public $role;
    public $password;
    public $is_archived;


    public function getHomePage() {
        if ($this->role === ROLE_ADMIN) {
            return 'admin_dashboard.php';
        }else if ($this->role === ROLE_MANAGER) {
            return 'admin_dashboard.php';
        }
        return 'index.php';
    }

    private static $currentUser = null;

    public function __construct($user)
    {
        $this->id = intval($user['id']);
        $this->name = $user['name'];
        $this->email = $user['email'];
        $this->role = $user['role'];
        $this->password = $user['password'];
        $this->is_archived = $user['is_archived'] ?? 0; // Default to 0 if not set
    }

    public static function getAuthenticatedUser()
    {
        if (!isset($_SESSION['user_id'])) return null;

        if (!static::$currentUser) {
            static::$currentUser = static::find($_SESSION['user_id']);
        }

        return static::$currentUser;
    }
 
    public static function all()
{
    global $connection;

    $query = 'SELECT * FROM `users` ORDER BY is_archived ASC, name ASC';

    $stmt = $connection->prepare($query);

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchAll();

    $result = array_map(fn($user) => new User($user), $result);

    return $result;
}

    public static function find($user_id) 
    {
        global $connection;

        $stmt = $connection->prepare("SELECT * FROM `users` WHERE id=:id");
        $stmt->bindParam("id", $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new User($result[0]);
        }

        return null;
    }

    public static function findByEmail($email) 
    {
        global $connection;

        $stmt = $connection->prepare("SELECT * FROM `users` WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        // Use fetch instead of fetchAll since you expect only one result
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        // If a user is found, return a new User object; otherwise, return null
        if ($result) {
            return new User($result); // Assumes User constructor can handle an associative array
        }

        return null; // Return null if no user was found
    }


    public static function login($email, $password) {
        if (empty($email)) throw new Exception("The email is required.");
        if (empty($password)) throw new Exception("The password is required.");
    
        // Find user by email
        $user = static::findByEmail($email);
    
        if (!$user) {
            throw new Exception('Account does not exist.');
        }

        if ($user->is_archived) {
            throw new Exception('This account has been removed from the system. Please contact the administrator.');
        }
    
        // Verify the entered password with the hashed password
        if (!password_verify($password, $user->password)) {
            throw new Exception('Incorrect password. Please try again.');
        }

        // Check if the password is the default (e.g., name123) or password change is required
        $defaultPassword = strtolower(str_replace(' ', '', $user->name)) . '123';
        if (password_verify($defaultPassword, $user->password) || $user->password_change_required) {
            $_SESSION['require_password_change'] = true; // Set flag to show the modal
        } else {
            unset($_SESSION['require_password_change']); // Clear the flag if not required
        }
    
        return $user;
    }

    public static function emailExists($email, $excludeId = null) {
        global $connection;
    
        $query = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = [':email' => $email];
    
        if ($excludeId) {
            $query .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
    
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
    
        return $stmt->fetchColumn() > 0;
    }

    public static function nameExists($name, $excludeId = null) {
        global $connection;
    
        $query = 'SELECT COUNT(*) FROM users WHERE name = :name';
        $params = [':name' => $name];
    
        if ($excludeId) {
            $query .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
    
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
    
        return $stmt->fetchColumn() > 0;
    }    
    
    
    public static function add($name, $email, $role, $password){
        $name = trim($name);
        $email = trim($email);
        if (self::emailExists($email)) {
            throw new Exception("Email already exists.");
        }

        if (self::nameExists($name)) {
            throw new Exception("Name already exists.");
        }
        
        global $connection;

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql_command = 'INSERT INTO users (name, email, role, password, password_change_required) VALUES (:name, :email, :role, :password, 1)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('name', $name);
        $stmt->bindParam('email', $email);
        $stmt->bindParam('role', $role);
        $stmt->bindParam('password', $hashedPassword);
        $stmt->execute();
    }


    public function update()
    {
        global $connection;

        $stmt = $connection->prepare('UPDATE users SET name=:name, email=:email, role=:role, password=:password, password_change_required = 0 WHERE id=:id');
        $stmt->bindParam('name', $this->name);
        $stmt->bindParam('email', $this->email);
        $stmt->bindParam('role', $this->role);
        $stmt->bindParam('password', $this->password);
        $stmt->bindParam('id', $this->id);
        $stmt->execute();

        // Clear the flag
        unset($_SESSION['require_password_change']);

        echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
    }

    public function archive() {
        global $connection;

        $stmt = $connection->prepare('UPDATE `users` SET `is_archived` = 1 WHERE `id` = :id');
        $stmt->bindParam('id', $this->id);
        $stmt->execute();
    }

    public static function getTotalUsers()
    {
        global $connection;

        $stmt = $connection->prepare('SELECT COUNT(*) as total_users FROM users WHERE is_archived = 0');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['total_users'];
        } else {
            return 0; // Return 0 if no users found or an error occurred
        }
    }
    
    public function logUserLogin($userId, $role)
    {
        global $connection;
        $query = "INSERT INTO user_logs (user_id, role, login_time) VALUES (:user_id, :role, NOW())";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':role' => $role
        ]);
    }
    
    public function logUserLogout($userId)
    {
        global $connection;
        $query = "UPDATE user_logs SET logout_time = NOW() WHERE user_id = :user_id AND logout_time IS NULL";
        $stmt = $connection->prepare($query);
        $stmt->execute([':user_id' => $userId]);
    }
    
}