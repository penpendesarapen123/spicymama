<?php

require_once __DIR__.'/../_init.php';

class CashDrawerLogs {
    public $id;
    public $transaction_number;
    public $payment;
    public $change;
    public $date_created;

    // Constructor to initialize properties
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->transaction_number = $data['transaction_number'] ?? null;
        $this->payment = $data['payment'] ?? null;
        $this->change = $data['change'] ?? null;
        $this->date_created = $data['date_created'] ?? null;
    }

    
    public static function addToDatabase($transactionNumber, $payment, $change, $dateCreated) {
        global $connection;
    
        try {
            $stmt = $connection->prepare('
                INSERT INTO cdraw_logs (transaction_number, payment, change, date_created) 
                VALUES (:transaction_number, :payment, :change, :date_created)
            ');
    
            $stmt->bindParam(':transaction_number', $transactionNumber, PDO::PARAM_STR);
            $stmt->bindParam(':payment', $payment, PDO::PARAM_STR);
            $stmt->bindParam(':change', $change, PDO::PARAM_STR);
            $stmt->bindParam(':date_created', $dateCreated, PDO::PARAM_STR);
    
            $stmt->execute();
    
            return true; // Indicate successful insertion
        } catch (PDOException $e) {
            error_log("Failed to add cash drawer log: " . $e->getMessage());
            return false; // Indicate failure
        }
    }
    
    

    // Fetch all logs
    public static function all() {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM `cdraw_logs` ORDER BY date_created DESC');
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($result) {
                return new self($result);
            }, $results);
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch cash drawer logs: {$e->getMessage()}");
        }
    }
}