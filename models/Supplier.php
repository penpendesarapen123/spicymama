<?php

// Assuming _init.php handles the database connection
require_once __DIR__ . '/../_init.php';

class Supplier
{
    public $supplier_id;
    public $supplier_name;
    public $phone_number;
    public $address;
    public $created_date;
    public $deleted_at;

    public function __construct($supplier)
    {
        $this->supplier_id = $supplier['supplier_id'];
        $this->supplier_name = $supplier['supplier_name'];
        $this->phone_number = $supplier['phone_number'];
        $this->address = $supplier['address'];
        $this->created_date = $supplier['created_date'];
        $this->deleted_at = $supplier['deleted_at'];
    }
    // Create a new supplier
    public static function create($supplier_name, $phone_number, $address) {
        global $connection;

        try {
            $sql_command = 'INSERT INTO suppliers (supplier_name, phone_number, address) VALUES (?, ?, ?)';
            $stmt = $connection->prepare($sql_command);
            $stmt->bindParam(1, $supplier_name);
            $stmt->bindParam(2, $phone_number);
            $stmt->bindParam(3, $address);
            $stmt->execute();

            return self::getLastRecord();
        } catch (PDOException $e) {
            throw new Exception("Failed to create supplier record: {$e->getMessage()}");
        }
    }

    // Update supplier details
    public static function update($supplier_id, $supplier_name, $phone_number, $address) {
        global $connection;

        try {
            $sql_command = 'UPDATE suppliers SET supplier_name = ?, phone_number = ?, address = ? WHERE supplier_id = ?';
            $stmt = $connection->prepare($sql_command);
            $stmt->bindParam(1, $supplier_name);
            $stmt->bindParam(2, $phone_number);
            $stmt->bindParam(3, $address);
            $stmt->bindParam(4, $supplier_id);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Failed to update supplier: {$e->getMessage()}");
        }
    }

    // Get the last inserted supplier
    public static function getLastRecord() {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM suppliers ORDER BY supplier_id DESC LIMIT 1');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new Supplier($result);
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch last supplier record: {$e->getMessage()}");
        }
    }

    // Get a supplier by its ID
    public static function findById($supplier_id) {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM suppliers WHERE supplier_id = ?');
            $stmt->bindParam(1, $supplier_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new Supplier($result);
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch supplier by ID: {$e->getMessage()}");
        }
    }

    // Get all suppliers
    public static function all($includeDeleted = false) {
        global $connection;

        $query = 'SELECT * FROM suppliers';
        if (!$includeDeleted) {
            $query .= ' WHERE deleted_at IS NULL';
        }

        try {
            $stmt = $connection->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($result) {
                return new Supplier($result);
            }, $results);
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch suppliers: {$e->getMessage()}");
        }
    }

    // Delete a supplier by its ID
    public static function delete($supplier_id)
{
    global $connection;

    try {
        $stmt = $connection->prepare('UPDATE suppliers SET deleted_at = NOW() WHERE supplier_id = :id');
        $stmt->bindParam('id', $supplier_id);
        $stmt->execute();
    } catch (PDOException $e) {
        throw new Exception("Failed to delete supplier: {$e->getMessage()}");
    }
}

public function restore()
{
    global $connection;

    $stmt = $connection->prepare('UPDATE suppliers SET deleted_at = NULL WHERE supplier_id = :id');
    $stmt->bindParam('id', $this->supplier_id);
    $stmt->execute();
}
public static function getTotalSuppliers()
{
    global $connection;

    try {
        // Modify the query to exclude deleted suppliers
        $stmt = $connection->prepare('SELECT COUNT(*) as total_suppliers FROM suppliers WHERE deleted_at IS NULL');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['total_suppliers'] : 0;
    } catch (PDOException $e) {
        throw new Exception("Failed to count suppliers: {$e->getMessage()}");
    }
}

}
?>
