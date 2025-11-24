<?php

require_once __DIR__.'/../_init.php';

class Discount
{
    public $discount_id;
    public $discount_name;
    public $discount_percentage;
    public $start_date;
    public $end_date;

    public function __construct($discount)
    {
        $this->discount_id = $discount['discount_id'];
        $this->discount_name = $discount['discount_name'];
        $this->discount_percentage = $discount['discount_percentage'];
        $this->start_date = $discount['start_date'];
        $this->end_date = $discount['end_date'];
    }

    public function update()
    {
        global $connection;

        $stmt = $connection->prepare('UPDATE discounts SET discount_name=:name, discount_percentage=:percentage, start_date=:start_date, end_date=:end_date WHERE discount_id=:id');
        $stmt->bindParam(':name', $this->discount_name);
        $stmt->bindParam(':percentage', $this->discount_percentage);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':id', $this->discount_id);
        $stmt->execute();
    }

    public function delete()
    {
        global $connection;

        $stmt = $connection->prepare('DELETE FROM discounts WHERE discount_id=:id');
        $stmt->bindParam(':id', $this->discount_id);
        $stmt->execute();
    }

    public static function all()
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM discounts');
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($item) {
            return new Discount($item);
        }, $result);
    }

    public static function add($name, $percentage, $start_date, $end_date)
    {
        global $connection;

        $stmt = $connection->prepare('INSERT INTO discounts (discount_name, discount_percentage, start_date, end_date) VALUES (:name, :percentage, :start_date, :end_date)');
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':percentage', $percentage);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
    }

    public static function find($id)
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM discounts WHERE discount_id=:id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Discount($result);
        } else {
            return null;
        }
    }
}
?>
