<?php

require_once __DIR__.'/../_init.php';

class Attendance {
    public $id;
    public $name;
    public $timein;
    public $timeout;
    public $date;

    public function __construct($attendance) {
        $this->id = $attendance['id'] ?? null;
        $this->name = $attendance['name'];
        $this->timein = $attendance['timein'];
        $this->timeout = $attendance['timeout'];
        $this->date = $attendance['date'];
    }

    public static function create($name, $timein, $date, $timeout = null) {
        global $connection;

        try {
            $sql_command = 'INSERT INTO attendance (name, timein, timeout, date) VALUES (?, ?, ?, ?)';
            $stmt = $connection->prepare($sql_command);
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $timein);
            if ($timeout === null) {
                $stmt->bindValue(3, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(3, $timeout);
            }
            $stmt->bindParam(4, $date);
            $stmt->execute();

            return self::getLastRecord();
        } catch (PDOException $e) {
            throw new Exception("Failed to create attendance record: {$e->getMessage()}");
        }
    }

    public static function updateTimeout($id, $timeout) {
        global $connection;

        try {
            $sql_command = 'UPDATE attendance SET timeout = ? WHERE id = ?';
            $stmt = $connection->prepare($sql_command);
            $stmt->bindParam(1, $timeout);
            $stmt->bindParam(2, $id);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Failed to update timeout: {$e->getMessage()}");
        }
    }

    public static function getLastRecord() {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM `attendance` ORDER BY id DESC LIMIT 1');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new Attendance($result);
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch last attendance record: {$e->getMessage()}");
        }
    }

    public static function getLastRecordByName($name) {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM `attendance` WHERE name = ? ORDER BY id DESC LIMIT 1');
            $stmt->bindParam(1, $name);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new Attendance($result);
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch last attendance record by name: {$e->getMessage()}");
        }
    }

    public static function all() {
        global $connection;

        try {
            $stmt = $connection->prepare('SELECT * FROM `attendance` ORDER BY date DESC, timein DESC');
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($result) {
                return new Attendance($result);
            }, $results);
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch attendance records: {$e->getMessage()}");
        }
    }
}
?>
