<?php 

require_once __DIR__.'/../_init.php';

class OrderItem 
{
    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;
    public $product_name;
    public $product_code; // Add product_code property
    public $product_idd;
    public $created_at; 
    public $seller_name;
    public $seller_role;
    public $transaction_number; 
    public $discountPercentage; 
    public $is_void;  // New property

    // Constructor to initialize the object properties
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->order_id = $data['order_id'];
        $this->product_id = $data['product_id'];
        $this->quantity = $data['quantity'];
        $this->price = $data['price'];
        $this->product_name = $data['product_name'];
        $this->product_code = $data['product_code']; // Initialize product_code
        $this->created_at = $data['created_at'];
        $this->seller_name = $data['seller_name']; 
        $this->seller_role = $data['seller_role']; 
        $this->product_idd = $data['product_idd']; 
        $this->transaction_number = $data['transaction_number'];
        $this->discountPercentage = $data['discountPercentage'];
        $this->is_void = $data['is_void'] ?? 0;  // New: Default to 0
    }



    public static function add($orderId, $item)
    {
        global $connection;

        $stmt = $connection->prepare('
            INSERT INTO `order_items`(order_id, product_id, quantity, price) 
            VALUES (:order_id, :product_id, :quantity, :price)
        ');
        
        // Bind the price from the cart item, not the default product price
        $stmt->bindParam("order_id", $orderId);
        $stmt->bindParam("product_id", $item['id']);
        $stmt->bindParam("quantity", $item['quantity']);
        $stmt->bindParam("price", $item['price']); // Use price from the cart item
        
        $stmt->execute();
    
        // Optionally update product quantity in stock, if needed
        $product = Product::find($item['id']);
        if ($product->category_id !== 24) { // Only update stock if not a service
            $product->quantity -= $item['quantity'];
            $product->update();
        }
    }


    public static function all()
{
    global $connection;

    $stmt = $connection->prepare('
        SELECT 
            order_items.*,
            order_items.product_id AS product_idd,
            products.name AS product_name,
            order_items.is_void,
            products.product_code, -- Include the product_code from the products table
            orders.created_at AS created_at,
            orders.name AS seller_name,
            orders.role AS seller_role,
            orders.transaction_number,          
            orders.discountPercentage           
        FROM order_items
        INNER JOIN products ON order_items.product_id = products.id
        INNER JOIN orders ON order_items.order_id = orders.id
        WHERE order_items.is_void = 0
        ORDER BY orders.created_at DESC
    ');

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchAll();

    // Map the result to the OrderItem objects with additional properties
    $result = array_map(function($item) {
        $orderItem = new OrderItem($item);
        $orderItem->transaction_number = $item['transaction_number'];    // Set the transaction number
        $orderItem->discountPercentage = $item['discountPercentage'];    // Set the discount percentage
        $orderItem->product_code = $item['product_code'];  // Set the product code
        return $orderItem;
    }, $result);

    return $result;
}

    public static function getTotalTransactionsToday()
    {
    global $connection;

    $sql_command = ("
        SELECT 
            COUNT(DISTINCT orders.id) AS total_transactions
        FROM 
            `orders` 
        WHERE Date(created_at) = CURDATE();
    ");

    $stmt = $connection->prepare($sql_command);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchColumn();

    return $result ? $result : 0; // Return the total transactions, or 0 if no transactions
    }

    public static function getWeeklyTransactions()
    {
    global $connection;

    $sql_command = ("
        SELECT 
            COUNT(DISTINCT orders.id) AS total_transactions
        FROM 
            `orders` 
        WHERE 
            YEARWEEK(orders.created_at, 1) = YEARWEEK(CURDATE(), 1)
    ");

    $stmt = $connection->prepare($sql_command);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchColumn();

    return $result ? $result : 0; // Return the total transactions, or 0 if no transactions
    }

    public static function getMonthlyTransactions()
    {
    global $connection;

    $sql_command = ("
        SELECT 
            COUNT(DISTINCT orders.id) AS total_transactions
        FROM 
            `orders` 
        WHERE 
            MONTH(orders.created_at) = MONTH(CURDATE())
            AND YEAR(orders.created_at) = YEAR(CURDATE())
    ");

    $stmt = $connection->prepare($sql_command);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchColumn();

    return $result ? $result : 0; // Return the total transactions, or 0 if no transactions
    }

    public static function getYearlyTransactions()
    {
    global $connection;

    $sql_command = ("
        SELECT 
            COUNT(DISTINCT orders.id) AS total_transactions
        FROM 
            `orders` 
        WHERE 
            YEAR(orders.created_at) = YEAR(CURDATE())
    ");

    $stmt = $connection->prepare($sql_command);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchColumn();

    return $result ? $result : 0; // Return the total transactions, or 0 if no transactions
    }

    public static function getTopSellingProductsPareto($limit = 5, $timeframe = 'yearly')
{
    global $connection;

    // Set the date range based on the timeframe
    $dateCondition = '';
    if ($timeframe === 'daily') {
        $dateCondition = "AND DATE(o.created_at) = CURDATE()";
    } elseif ($timeframe === 'weekly') {
        $dateCondition = "AND YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($timeframe === 'monthly') {
        $dateCondition = "AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
    } elseif ($timeframe === 'yearly') {
        $dateCondition = "AND YEAR(o.created_at) = YEAR(CURDATE())";
    }

    $sql = "
        SELECT 
            oi.product_id, 
            p.name AS product_name,
            p.product_code, -- Include product_code
            SUM(oi.quantity) AS quantity_sold,
            SUM(oi.quantity * oi.price) AS total_sales
        FROM 
            order_items oi
        INNER JOIN 
            products p ON oi.product_id = p.id
        INNER JOIN 
            orders o ON oi.order_id = o.id
        WHERE
            p.deleted_at IS NULL
            AND p.category_id != 24
            AND oi.is_void = 0
            $dateCondition
            
        GROUP BY 
            p.name
        ORDER BY 
            quantity_sold DESC
        LIMIT :limit";

    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public static function delete($orderItemId)
{
    global $connection;

    try {
        // Start a transaction
        $connection->beginTransaction();

        // Get the order item details to update the product stock
        $stmt = $connection->prepare('SELECT product_id, quantity FROM order_items WHERE id = :id');
        $stmt->bindParam(':id', $orderItemId, PDO::PARAM_INT);
        $stmt->execute();
        $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderItem) {
            throw new Exception("Order item not found.");
        }

        // Update the stock quantity for the associated product if it is not a service
        $product = Product::find($orderItem['product_id']);
        if ($product && $product->category_id !== 24) {
            $product->quantity += $orderItem['quantity'];
            $product->update();
        }

        
// Soft-delete: Set is_void = 1 and quantity = 0 instead of hard delete
            $stmt = $connection->prepare('UPDATE order_items SET is_void = 1, quantity = 0 WHERE id = :id');
            $stmt->bindParam(':id', $orderItemId, PDO::PARAM_INT);
            $stmt->execute();

        // Commit the transaction
        $connection->commit();

        return true;
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $connection->rollBack();
        throw $e;
    }
}

public static function getCashierReport($startDate, $endDate)
{
    global $connection;

    $stmt = $connection->prepare('
        SELECT 
            o.name AS cashier_name,
            o.role AS cashier_role,
            SUM(oi.quantity * oi.price * (1 - (o.discountPercentage / 100))) AS total_sales,
            COUNT(o.id) AS total_transactions
        FROM 
            orders o
        INNER JOIN 
            order_items oi ON o.id = oi.order_id
        WHERE 
            o.created_at BETWEEN :start_date AND :end_date
            AND oi.is_void = 0
        GROUP BY 
            o.name, o.role
        ORDER BY 
            total_sales DESC;
    ');

    // Bind parameters to filter by the date range
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    return $stmt->fetchAll();
}

// FIXED: Updated for full void (delete order_item), stock restore (non-services only), and void_logs entry
   // Updated: No void_logs insert. Use UPDATE for soft-void. Restore stock for returned qty.
    public static function returnItem($orderItemId, $returnQuantity)
    {
        global $connection;

        try {
            $connection->beginTransaction();

            // Get the order item details with joins for logging (but no log now)
            $stmt = $connection->prepare('
                SELECT 
                    oi.quantity AS original_quantity, 
                    oi.product_id,
                    p.product_code,
                    o.transaction_number
                FROM order_items oi
                INNER JOIN products p ON oi.product_id = p.id
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.id = :id
            ');
            $stmt->bindParam(':id', $orderItemId);
            $stmt->execute();
            $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orderItem) {
                throw new Exception('Order item not found.');
            }

            if ($returnQuantity > $orderItem['original_quantity']) {
                throw new Exception('Return quantity exceeds purchased quantity.');
            }

            $restoredQty = $returnQuantity;

            if ($returnQuantity == $orderItem['original_quantity']) {
                // Full void: Set quantity=0, is_void=1
                $stmt = $connection->prepare('UPDATE order_items SET quantity = 0, is_void = 1 WHERE id = :id');
                $stmt->bindParam(':id', $orderItemId);
                $stmt->execute();
            } else {
                // Partial return: Reduce quantity only
                $stmt = $connection->prepare('UPDATE order_items SET quantity = quantity - :return_qty WHERE id = :id');
                $stmt->bindParam(':return_qty', $returnQuantity);
                $stmt->bindParam(':id', $orderItemId);
                $stmt->execute();
            }

            // Restore stock only for non-services
            $product = Product::find($orderItem['product_id']);
            if ($product && $product->category_id !== 24) {
                $product->quantity += $restoredQty;
                $product->update();
            }

            // No void_logs insert

            $connection->commit();
            return ['success' => true, 'message' => 'Order voided successfully.'];
        } catch (Exception $e) {
            $connection->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
