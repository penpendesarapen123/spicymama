<?php

require_once __DIR__.'/../_init.php';

class Product 
{
    public $id;

    public $product_code;

    public $name;
    public $category_id;
    public $quantity;
    public $price;
    public $size;
    public $image;
    public $supplier;
    public $supplier_id; // Declare it as a public property
    public $created_at;
    public $deleted_at;
    public $category;

    public $return_at;

    public function __construct($product)
{
    $this->id = $product['id'];
    $this->product_code = $product['product_code'];
    $this->name = $product['name'];
    $this->category_id = $product['category_id'];
    $this->quantity = intval($product['quantity']);
    $this->price = floatval($product['price']);
    $this->size = $product['size'];
    $this->image = $product['image'];
    $this->supplier_id = $product['supplier_id'];
    $this->created_at = $product['created_at'];
    $this->deleted_at = $product['deleted_at']; // Initialize deleted_at
    $this->return_at = $product['return_at'];
    $this->category = $this->getCategory($product);
}


private function getCategory($product)
{
    if (isset($product['category_name'])) {
        return new Category([
            'id' => $product['category_id'],
            'name' => $product['category_name']
        ]);
    }

    return Category::find($product['category_id']);
}

public function update()
{
    global $connection;

    $stmt = $connection->prepare('UPDATE products SET name=:name, category_id=:category_id, quantity=:quantity, price=:price, size=:size, image=:image, supplier_id=:supplier_id, product_code=:product_code WHERE id=:id');
    $stmt->bindParam('name', $this->name);
    $stmt->bindParam('category_id', $this->category_id);
    $stmt->bindParam('quantity', $this->quantity);
    $stmt->bindParam('price', $this->price);
    $stmt->bindParam('size', $this->size);
    $stmt->bindParam('image', $this->image);
    $stmt->bindParam('supplier_id', $this->supplier_id);
    $stmt->bindParam('product_code', $this->product_code); // Bind the product_code
    $stmt->bindParam('id', $this->id);

    $stmt->execute();

    // Return true if rows were affected, false otherwise
    return $stmt->rowCount() > 0;
}



    public function addStock(){
        global $connection;

        $updateSql = 'UPDATE products SET quantity = quantity + :quantity WHERE id = :id';
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $updateStmt->execute();

        $updatedBy = $_SESSION['user'] ?? 'Unknown'; // Use the current logged-in user
        $logSql = 'INSERT INTO inventory_log (product_id, quantity_added, supplier_name, updated_by) 
                   VALUES (:product_id, :quantity_added, :supplier_name, :updated_by)';
        $logStmt = $connection->prepare($logSql);
        $logStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $logStmt->bindParam(':quantity_added', $quantity, PDO::PARAM_INT);
        $logStmt->bindParam(':supplier_name', $supplierName, PDO::PARAM_STR);
        $logStmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_STR);
        $logStmt->execute();
    }

    public static function inventoryLogs(){
        global $connection;

        $stmt = $connection->prepare('SELECT il.*, p.name AS product_name  
        FROM inventory_log il 
        JOIN products p ON il.product_id = p.id 
        ORDER BY il.updated_at DESC');

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        return $result;
    }

    
    public static function getNewlyAddedItems($days = 7) {
        global $connection; // Assuming $db is your database connection
        try {
            $query = "
                SELECT 
                    p.id, 
                    p.product_code, 
                    p.name, 
                    CASE 
                        WHEN c.id = 24 THEN 'N/A' 
                        ELSE p.quantity 
                    END AS quantity, 
                    p.price, 
                    p.size, 
                    p.created_at 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                  AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
            ";
            $stmt = $connection->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching newly added products: " . $e->getMessage());
            return [];
        }
    }
    
    

    public function delete()
    {
        $this->softDelete();
    }

    public function softDelete()
{
    global $connection;

    $stmt = $connection->prepare('UPDATE products SET deleted_at = NOW() WHERE id = :id');
    $stmt->bindParam('id', $this->id);
    $stmt->execute();
}

public static function getSoftDeleted()
{
    global $connection;

    $stmt = $connection->prepare('
        SELECT p.*, s.supplier_name 
        FROM products p 
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.deleted_at IS NOT NULL -- Only soft-deleted products
        ORDER BY p.deleted_at DESC
    ');
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchAll();

    // Map the results to Product objects
    $result = array_map(function($item) {
        $product = new Product($item);
        
        // Assign supplier info to the product if it exists
        if (isset($item['supplier_name'])) {
            $product->supplier = (object) ['supplier_name' => $item['supplier_name']];
        } else {
            $product->supplier = null; // No supplier
        }
        
        return $product;
    }, $result);

    return $result;
}


public function restore()
{
    global $connection;

    $stmt = $connection->prepare('UPDATE products SET deleted_at = NULL WHERE id = :id');
    $stmt->bindParam('id', $this->id);
    $stmt->execute();
}

public static function all($limit = null)
{
    global $connection;

    // Base query to fetch products
    $query = '
        SELECT p.*, s.supplier_name 
        FROM products p 
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.deleted_at IS NULL -- Exclude soft-deleted products
        ORDER BY p.created_at DESC -- Order by most recent entries
    ';

    // Add LIMIT clause if $limit is provided
    if ($limit !== null) {
        $query .= ' LIMIT :limit';
    }

    $stmt = $connection->prepare($query);

    // Bind the limit parameter if it exists
    if ($limit !== null) {
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $result = $stmt->fetchAll();

    // Map the results to Product objects
    $result = array_map(function ($item) {
        $product = new Product($item);
        
        // Assign supplier info to the product if it exists
        if (isset($item['supplier_name'])) {
            $product->supplier = (object)['supplier_name' => $item['supplier_name']];
        } else {
            $product->supplier = null; // No supplier
        }
        
        return $product;
    }, $result);

    return $result;
}


public static function find($id)
{
    global $connection;

    try {
        // Prepare the query
        $stmt = $connection->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Specify the parameter type
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        // Fetch the single result
        $result = $stmt->fetch(); // Fetch a single row (not fetchAll)

        // Check if a result was found and return a Product instance
        if ($result) {
            return new Product($result);
        }

        // Return null if no result is found
        return null;
    } catch (PDOException $e) {
        // Log the error or handle it gracefully
        error_log('Error in Product::find: ' . $e->getMessage());
        return null; // Return null on failure
    }
}

    public static function add($name, $category_id, $quantity, $price, $size, $image, $supplier_id, $product_code)
    {
        global $connection;

        $sql_command = 'INSERT INTO products (name, category_id, quantity, price, size, image, supplier_id, product_code) VALUES (:name, :category_id, :quantity, :price, :size, :image, :supplier_id, :product_code)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('name', $name);
        $stmt->bindParam('category_id', $category_id);
        $stmt->bindParam('quantity', $quantity);
        $stmt->bindParam('price', $price);
        $stmt->bindParam('size', $size);
        $stmt->bindParam('image', $image);
        $stmt->bindParam('supplier_id', $supplier_id);
        $stmt->bindParam('product_code', $product_code);
        $stmt->execute();
    }

    public static function isProductCodeUnique($productCode, $excludeProductId = null)
    {
        global $connection;

        $sql = "SELECT COUNT(*) FROM products WHERE product_code = :product_code";
        if ($excludeProductId) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $connection->prepare($sql);
        $stmt->bindParam('product_code', $productCode);
        if ($excludeProductId) {
            $stmt->bindParam('exclude_id', $excludeProductId);
        }
        $stmt->execute();

        return $stmt->fetchColumn() == 0; // Returns true if no matching product code is found
    }

    public static function countOutOfStock()
    {
        global $connection;
    
        $stmt = $connection->prepare('
            SELECT COUNT(*) AS out_of_stock_count 
            FROM products 
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE products.quantity = 0 
            AND products.deleted_at IS NULL -- Exclude soft-deleted products
            AND categories.name != "Services" -- Exclude the "Services" category
            AND categories.deleted_at IS NULL -- Exclude soft-deleted categories
        ');
        $stmt->execute();
        $outOfStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock_count'];
    
        return $outOfStockCount;
    }
    
    public static function countRunningLowStock()
    {
        global $connection;
    
        $stmt = $connection->prepare('
            SELECT COUNT(*) AS running_low_count 
            FROM products 
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE products.quantity > 0
            AND (
                (categories.name IN ("bike", "bicycle") AND products.quantity <= 3) OR 
                (categories.name NOT IN ("bike", "bicycle") AND products.quantity <= 5)
            )
            AND products.deleted_at IS NULL -- Exclude soft-deleted products
            AND categories.name != "Services" -- Exclude the "Services" category
            AND categories.deleted_at IS NULL -- Exclude soft-deleted categories
        ');
        $stmt->execute();
        $runningLowCount = $stmt->fetch(PDO::FETCH_ASSOC)['running_low_count'];
    
        return $runningLowCount;
    }
    
    public static function countFullStock()
    {
        global $connection;
    
        $stmt = $connection->prepare('
            SELECT COUNT(*) AS full_stock_count 
            FROM products 
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE (
                (categories.name IN ("bike", "bicycle") AND products.quantity BETWEEN 4 AND 5) OR 
                (categories.name NOT IN ("bike", "bicycle") AND products.quantity BETWEEN 6 AND 10)
            )
            AND products.deleted_at IS NULL -- Exclude soft-deleted products
            AND categories.name != "Services" -- Exclude the "Services" category
            AND categories.deleted_at IS NULL -- Exclude soft-deleted categories
        ');
        $stmt->execute();
        $fullStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['full_stock_count'];
    
        return $fullStockCount;
    }
    
    public static function countOverStock()
    {
        global $connection;
    
        $stmt = $connection->prepare('
            SELECT COUNT(*) AS over_stock_count 
            FROM products 
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE (
                (categories.name IN ("bike", "bicycle") AND products.quantity > 5) OR 
                (categories.name NOT IN ("bike", "bicycle") AND products.quantity > 10)
            )
            AND products.deleted_at IS NULL -- Exclude soft-deleted products
            AND categories.name != "Services" -- Exclude the "Services" category
            AND categories.deleted_at IS NULL -- Exclude soft-deleted categories
        ');
        $stmt->execute();
        $overStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['over_stock_count'];
    
        return $overStockCount;
    }
    
    
    

public static function getTotalPriceOfProducts()
{
    global $connection;

    $stmt = $connection->prepare('
        SELECT SUM(price * quantity) AS total_price 
        FROM products 
        WHERE deleted_at IS NULL -- Exclude soft-deleted products
    ');
    $stmt->execute();
    $totalPrice = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'];

    return $totalPrice;
}

public static function getSalesByProductIds($productIds, $filter = 'today', $year = null, $month = null)
{
    global $connection;

    if (empty($productIds)) { 
        return [];
    }

    // Generate placeholders for named parameters
    $placeholders = [];
    foreach ($productIds as $index => $productId) {
        $placeholders[] = ":productId{$index}";
    }
    $placeholdersString = implode(',', $placeholders);

    // Base query
    $query = "
        SELECT 
            products.product_code,
            products.name,
            COALESCE(SUM(order_items.quantity), 0) AS sales,
            COALESCE(SUM(order_items.price * order_items.quantity * (1 - orders.discountPercentage / 100)), 0) AS total_sales_amount
        FROM 
            products
        LEFT JOIN 
            order_items ON products.id = order_items.product_id AND order_items.is_void = 0
        LEFT JOIN 
            orders ON order_items.order_id = orders.id
        WHERE 
            products.id IN ($placeholdersString)
    ";

    // Add date filters
    if ($filter === 'today') {
        $query .= " AND DATE(orders.created_at) = CURDATE()";
    } elseif ($filter === 'month' && $year !== null && $month !== null) {
        $query .= " AND YEAR(orders.created_at) = :year AND MONTH(orders.created_at) = :month";
    }

    // Group by product
    $query .= " GROUP BY products.id, products.product_code, products.name";

    $stmt = $connection->prepare($query);

    // Bind product IDs as named parameters
    foreach ($productIds as $index => $productId) {
        $stmt->bindValue(":productId{$index}", $productId, PDO::PARAM_INT);
    }

    // Bind year and month if filter is 'month'
    if ($filter === 'month' && $year !== null && $month !== null) {
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
    }

    $stmt->execute();

    // Fetch and return results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




}
