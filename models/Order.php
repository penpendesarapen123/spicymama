<?php

require_once __DIR__.'/../_init.php';

class Order
{
    public $id;
    public $name;
    public $role;
    public $discountPercentage;
    public $created_at;
    public $transaction_number;
    public $payment_info;
    public $change_info;

    public function __construct($order)
    {
        $this->id = $order['id'];
        $this->name = $order['name'];
        $this->role = $order['role'];
        $this->discountPercentage = $order['discountPercentage'];
        $this->created_at = $order['created_at'];
        $this->transaction_number = $order['transaction_number'];
        $this->payment_info = $order['payment_info'];
        $this->change_info = $order['change_info'];
    }

    public static function create($name, $role, $discountPercentage = 0, $payment_info = 0, $change_info = 0)
    {
        global $connection;

        // Generate a unique transaction number
        $transactionNumber = static::generateNextTransactionNumber();

        $sql_command = '
            INSERT INTO orders (name, role, discountPercentage, transaction_number, payment_info, change_info) 
            VALUES (:name, :role, :discountPercentage, :transaction_number, :payment_info, :change_info)
        ';
        
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':discountPercentage', $discountPercentage);
        $stmt->bindParam(':transaction_number', $transactionNumber);
        $stmt->bindParam(':payment_info', $payment_info);
        $stmt->bindParam(':change_info', $change_info);
        
        $stmt->execute();

        

        return static::getLastRecord();
    }

    private static function generateNextTransactionNumber()
    {
        global $connection;

        // Get the last transaction number
        $stmt = $connection->prepare('SELECT transaction_number FROM orders ORDER BY id DESC LIMIT 1;');
        $stmt->execute();
        $lastTransaction = $stmt->fetchColumn();

        if ($lastTransaction) {
            // Increment the last transaction number
            $letters = substr($lastTransaction, 0, 2);
            $number = (int)substr($lastTransaction, 3) + 1;
            $nextTransactionNumber = $letters . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
        } else {
            // Start from a default transaction number if no previous exists
            $letters = chr(rand(65, 90)) . chr(rand(65, 90)); // Random 2 letters
            $nextTransactionNumber = $letters . '-00001';
        }

        return $nextTransactionNumber;
    }

    public static function getLastRecord()
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM `orders` ORDER BY id DESC LIMIT 1;');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new Order($result[0]);
        }

        return null;
    }

    public static function getAllTransactionLogs()
{
    global $connection;

    $stmt = $connection->prepare('
        SELECT 
            transaction_number, 
            payment_info, 
            change_info, 
            created_at AS date_created
        FROM 
            orders 
        ORDER BY 
            id DESC;
    ');
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    return $stmt->fetchAll();
}
}