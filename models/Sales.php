<?php

require_once __DIR__.'/../_init.php';

class Sales
{
    public static function TodaySales()
    {
        global $connection;

        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS today
            FROM 
                order_items 
            INNER JOIN 
                orders ON order_items.order_id = orders.id 
            WHERE 
                DATE(orders.created_at) = CURDATE()
        ";

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function WeeklySales() {
        global $connection;
        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS total
            FROM 
                order_items
            INNER JOIN 
                orders ON order_items.order_id = orders.id
            WHERE 
                YEARWEEK(orders.created_at, 1) = YEARWEEK(CURDATE(), 1)
        ";
        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }
    
    public static function MonthlySales() {
        global $connection;
        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS total
            FROM 
                order_items
            INNER JOIN 
                orders ON order_items.order_id = orders.id
            WHERE 
                MONTH(orders.created_at) = MONTH(CURDATE())
                AND YEAR(orders.created_at) = YEAR(CURDATE())
        ";
        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }
    
    public static function YearlySales() {
        global $connection;
        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS total
            FROM 
                order_items
            INNER JOIN 
                orders ON order_items.order_id = orders.id
            WHERE 
                YEAR(orders.created_at) = YEAR(CURDATE())
        ";
        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getTodayProductsSold($weighted = false)
    {
        global $connection;

        if ($weighted) {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity * (1 - orders.discountPercentage / 100)) AS total_weighted_quantity
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    DATE(orders.created_at) = CURDATE()
            ";
        } else {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity) AS total_products_sold
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    DATE(orders.created_at) = CURDATE()
            ";
        }

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getWeeklyProductsSold($weighted = false)
    {
        global $connection;

        if ($weighted) {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity * (1 - orders.discountPercentage / 100)) AS total_weighted_quantity
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    YEARWEEK(orders.created_at, 1) = YEARWEEK(CURDATE(), 1)
            ";
        } else {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity) AS total_products_sold
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    YEARWEEK(orders.created_at, 1) = YEARWEEK(CURDATE(), 1)
            ";
        }

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getMonthlyProductsSold($weighted = false)
    {
        global $connection;

        if ($weighted) {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity * (1 - orders.discountPercentage / 100)) AS total_weighted_quantity
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    MONTH(orders.created_at) = MONTH(CURDATE())
                    AND YEAR(orders.created_at) = YEAR(CURDATE())
            ";
        } else {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity) AS total_products_sold
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    MONTH(orders.created_at) = MONTH(CURDATE())
                    AND YEAR(orders.created_at) = YEAR(CURDATE())
            ";
        }

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getYearlyProductsSold($weighted = false)
    {
        global $connection;

        if ($weighted) {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity * (1 - orders.discountPercentage / 100)) AS total_weighted_quantity
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    YEAR(orders.created_at) = YEAR(CURDATE())
            ";
        } else {
            $sql_command = "
                SELECT 
                    SUM(order_items.quantity) AS total_products_sold
                FROM 
                    order_items 
                INNER JOIN 
                    orders ON order_items.order_id = orders.id 
                WHERE 
                    YEAR(orders.created_at) = YEAR(CURDATE())
            ";
        }

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }


    public static function getMonthlySales($year = null, $month = null)
    {
        global $connection;

        $yearFilter = $year ? "YEAR(orders.created_at) = :year" : "YEAR(orders.created_at) = YEAR(CURRENT_DATE)";
        $monthFilter = $month ? "MONTH(orders.created_at) = :month" : "MONTH(orders.created_at) = MONTH(CURRENT_DATE)";

        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS monthly_sales
            FROM 
                order_items 
            INNER JOIN 
                orders ON order_items.order_id = orders.id 
            WHERE 
                $yearFilter AND $monthFilter
        ";

        $stmt = $connection->prepare($sql_command);

        if ($year) {
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        }

        if ($month) {
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getAnnualSales($year = null)
    {
        global $connection;

        $yearFilter = $year ? "YEAR(orders.created_at) = :year" : "YEAR(orders.created_at) = YEAR(CURRENT_DATE)";

        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS annual_sales
            FROM 
                order_items 
            INNER JOIN 
                orders ON order_items.order_id = orders.id 
            WHERE 
                $yearFilter
        ";

        $stmt = $connection->prepare($sql_command);

        if ($year) {
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getTotalSales()
    {
        global $connection;

        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS total
            FROM 
                order_items 
            INNER JOIN 
                orders ON order_items.order_id = orders.id
        ";

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getDailySales($year, $month)
    {
        global $connection;

        $sql_command = "
            SELECT 
                SUM(order_items.quantity * order_items.price * (1 - orders.discountPercentage / 100)) AS daily_sales, 
                DATE(orders.created_at) AS sale_date
            FROM 
                order_items 
            INNER JOIN 
                orders ON order_items.order_id = orders.id 
            WHERE 
                YEAR(orders.created_at) = :year AND MONTH(orders.created_at) = :month
            GROUP BY 
                sale_date
            ORDER BY 
                sale_date
        ";

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll();
        $dailySalesData = [];

        foreach ($result as $row) {
            $dailySalesData[$row['sale_date']] = $row['daily_sales'];
        }

        return $dailySalesData;
    }

    
    public static function getTodayTransactions()
    {
        global $connection;

        $sql = "
            SELECT 
                orders.transaction_number,
                orders.created_at AS date,
                orders.name AS seller_name,
                orders.role AS seller_role,
                products.product_code,
                order_items.price,
                order_items.quantity,
                orders.discountPercentage
            FROM 
                orders
            INNER JOIN 
                order_items ON orders.id = order_items.order_id
            INNER JOIN 
                products ON order_items.product_id = products.id
            WHERE 
                DATE(orders.created_at) = CURDATE()
            ORDER BY 
                orders.created_at DESC
        ";

        $stmt = $connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function getMonthlyTransactions($year = null, $month = null)
    {
        global $connection;

        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $sql = "
            SELECT 
                orders.transaction_number,
                orders.created_at AS date,
                orders.name AS seller_name,
                orders.role AS seller_role,
                products.product_code,
                order_items.price,
                order_items.quantity,
                orders.discountPercentage
            FROM 
                orders
            INNER JOIN 
                order_items ON orders.id = order_items.order_id
            INNER JOIN 
                products ON order_items.product_id = products.id
            WHERE 
                YEAR(orders.created_at) = :year AND MONTH(orders.created_at) = :month
            ORDER BY 
                orders.created_at DESC
        ";

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
