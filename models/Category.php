<?php

require_once __DIR__.'/../_init.php';

class Category
{
    public $id;
    public $name;
    public $created_at;
    public $deleted_at;

    private static $cache = null;

    public function __construct($category)
    {
        $this->id = $category['id'];
        $this->name = $category['name'];
        $this->created_at = $category['created_at'];
        $this->deleted_at = $category['deleted_at'];
    }

    public function update()
    {
        global $connection;

        // Check if name is unique (excluding this category)
        $category = self::findByName($this->name);
        if ($category && $category->id !== $this->id) {
            throw new Exception('Name already exists.');
        }

        $stmt = $connection->prepare('UPDATE categories SET name=:name WHERE id=:id');
        $stmt->bindParam('name', $this->name);
        $stmt->bindParam('id', $this->id);
        $stmt->execute();
    }

    public function delete() {

    $this->softDelete();    

    }

    public function softDelete()
    {
        global $connection;

        $stmt = $connection->prepare('UPDATE categories SET deleted_at = NOW() WHERE id = :id');
        $stmt->bindParam('id', $this->id);
        $stmt->execute();
    }

    public function restore()
    {
        global $connection;

        $stmt = $connection->prepare('UPDATE categories SET deleted_at = NULL WHERE id = :id');
        $stmt->bindParam('id', $this->id);
        $stmt->execute();
    }

    public static function all($includeDeleted = false)
    {
        global $connection;
    
        if (static::$cache) {
            return static::$cache;
        }
    
        $query = 'SELECT * FROM categories';
        
        // Only include non-deleted items unless explicitly including deleted items
        if (!$includeDeleted) {
            $query .= ' WHERE deleted_at IS NULL';
        }
        
        // Order by deleted_at (non-deleted first), then alphabetically by name
        $query .= ' ORDER BY 
                    deleted_at IS NOT NULL,  -- Non-deleted items first
                    name ASC';              
    
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
        $result = $stmt->fetchAll();
        static::$cache = array_map(function ($item) {
            return new Category($item);
        }, $result);
    
        return static::$cache;
    }
    


    public static function add($name)
    {
        global $connection;

        if (self::findByName($name)) {
            throw new Exception('Name already exists');
        }

        $stmt = $connection->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->bindParam('name', $name);
        $stmt->execute();
    }

    public static function find($id)
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetch();

        return $result ? new Category($result) : null;
    }

    public static function findByName($name)
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM categories WHERE name = :name');
        $stmt->bindParam('name', $name);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetch();

        return $result ? new Category($result) : null;
    }
    
    public static function getProductsCountPerCategory()
    {
        global $connection;
    
        $sql_command = ("
            SELECT 
                c.id AS category_id,
                c.name AS category_name,
                COUNT(p.id) AS product_count
            FROM 
                categories c
            LEFT JOIN 
                products p ON c.id = p.category_id
                AND p.deleted_at IS NULL -- Exclude soft-deleted products
            WHERE 
                c.deleted_at IS NULL -- Exclude soft-deleted categories
            GROUP BY 
                c.id;
        ");
    
        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
        return $stmt->fetchAll();
    }
    

    public static function getStockLevelperCategory()
    {
        global $connection;

        $sql_command = ("
            SELECT 
                categories.id AS category_id, -- Include category ID
                categories.name AS category_name,
                SUM(CASE WHEN products.quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock_count, -- Out of stock
                SUM(CASE WHEN products.quantity > 0 AND products.quantity <= 5 THEN 1 ELSE 0 END) AS running_low_count, -- Running low (threshold = 5)
                SUM(CASE WHEN products.quantity >= 6 AND products.quantity <= 10 THEN 1 ELSE 0 END) AS fully_stocked_count, -- Fully stocked (6 to 10)
                SUM(CASE WHEN products.quantity > 10 THEN 1 ELSE 0 END) AS overstock_count -- Overstock (threshold > 10)
            FROM 
                categories
            LEFT JOIN 
                products ON categories.id = products.category_id
            WHERE 
                categories.deleted_at IS NULL -- Exclude soft-deleted categories
                AND categories.name != 'Services' -- Exclude the 'Services' category
            GROUP BY 
                categories.id, categories.name; -- Include categories.name in GROUP BY
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }
    public static function getSalesByCategory($timeframe = 'daily')
{
    global $connection;

    // Determine the date range based on the timeframe
    $dateCondition = "";
    switch ($timeframe) {
        case 'daily':
            $dateCondition = "AND DATE(orders.created_at) = CURDATE()";
            break;
        case 'weekly':
            $dateCondition = "AND YEARWEEK(orders.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'monthly':
            $dateCondition = "AND MONTH(orders.created_at) = MONTH(CURDATE()) AND YEAR(orders.created_at) = YEAR(CURDATE())";
            break;
        case 'yearly':
            $dateCondition = "AND YEAR(orders.created_at) = YEAR(CURDATE())";
            break;
        default:
            throw new Exception("Invalid timeframe");
    }

    $sql_command = ("
        SELECT 
            categories.name AS category_name,
            SUM(order_items.quantity * order_items.price) AS total_sales
        FROM 
            categories
        LEFT JOIN 
            products ON categories.id = products.category_id
        LEFT JOIN 
            order_items ON products.id = order_items.product_id
        LEFT JOIN 
            orders ON order_items.order_id = orders.id
        WHERE 
            categories.deleted_at IS NULL -- Exclude soft-deleted categories
            AND products.deleted_at IS NULL -- Exclude soft-deleted products
            $dateCondition -- Apply the timeframe filter
        GROUP BY 
            categories.id
    ");

    $stmt = $connection->prepare($sql_command);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    return $stmt->fetchAll();
}
    

}