<?php
require_once __DIR__.'/../_init.php';

$data = [];
$timeframe = $_GET['timeframe'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$startDateFormatted = date('Y-m-d', strtotime($startDate));
$endDateFormatted = date('Y-m-d', strtotime($endDate));

if ($timeframe) {
    switch ($timeframe) {
        case 'hour':
            $date = $singleDate ?? date('Y-m-d');

            $stmt = $connection->prepare("
                SELECT 
                    HOUR(orders.created_at) AS hour,
                    SUM(order_items.quantity * order_items.price) AS sales
                FROM orders
                JOIN order_items ON orders.id = order_items.order_id
                WHERE DATE(orders.created_at) = :date
                GROUP BY hour
                ORDER BY hour
            ");
            $stmt->execute(['date' => $date]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = range(0, 23);
            $sales = array_fill(0, 24, 0);

            foreach ($result as $row) {
                $sales[$row['hour']] = $row['sales'];
            }

            $labelsFormatted = array_map(function ($h) {
                return "{$h}:00";  // Format hour as "hour:00"
            }, $labels);

            $data = ['labels' => $labelsFormatted, 'sales' => $sales];
            break;

            case 'week':
                $date = $singleDate ?? date('Y-m-d');
                $yearWeek = date('oW', strtotime($date));
            
                $stmt = $connection->prepare("
                    SELECT 
                        DATE(orders.created_at) AS date,
                        SUM(order_items.quantity * order_items.price) AS sales
                    FROM orders
                    JOIN order_items ON orders.id = order_items.order_id
                    WHERE YEARWEEK(orders.created_at, 1) = :yearWeek
                    GROUP BY date
                    ORDER BY date
                ");
                $stmt->execute(['yearWeek' => $yearWeek]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                // Define the days of the week for X-axis labels
                $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
                // Initialize sales array with zeros for each day of the week
                $sales = array_fill(0, 7, 0);
            
                // Map sales data to the correct day of the week
                foreach ($result as $row) {
                    $dayIndex = date('w', strtotime($row['date'])); // Get the day index (0 = Sunday, 6 = Saturday)
                    $sales[$dayIndex] = $row['sales'];
                }
            
                $data = ['labels' => $daysOfWeek, 'sales' => $sales];
                break;
            
            

                case 'month':
                    $date = $singleDate ?? date('Y-m-d');
                    $month = date('m', strtotime($date));
                    $year = date('Y', strtotime($date));
                
                    $stmt = $connection->prepare("
                        SELECT 
                            DAY(orders.created_at) AS day,
                            SUM(order_items.quantity * order_items.price) AS sales
                        FROM orders
                        JOIN order_items ON orders.id = order_items.order_id
                        WHERE MONTH(orders.created_at) = :month
                        AND YEAR(orders.created_at) = :year
                        GROUP BY day
                        ORDER BY day
                    ");
                    $stmt->execute(['month' => $month, 'year' => $year]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    // Generate labels for each day of the month
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $labels = [];
                    $sales = array_fill(0, $daysInMonth, 0);
                
                    for ($i = 1; $i <= $daysInMonth; $i++) {
                        $labels[] = date('M j, Y', strtotime("$year-$month-$i")); // Format each day for the X-axis
                    }
                
                    // Map sales data to the correct day
                    foreach ($result as $row) {
                        $sales[$row['day'] - 1] = $row['sales'];
                    }
                
                    $data = ['labels' => $labels, 'sales' => $sales];
                    break;
                
                
        case 'year':
            $date = $singleDate ?? date('Y-m-d');
            $year = date('Y', strtotime($date));

            $stmt = $connection->prepare("
                SELECT 
                    MONTH(orders.created_at) AS month_num,
                    MONTHNAME(orders.created_at) AS month,
                    SUM(order_items.quantity * order_items.price) AS sales
                FROM orders
                JOIN order_items ON orders.id = order_items.order_id
                WHERE YEAR(orders.created_at) = :year
                GROUP BY month_num, month
                ORDER BY month_num
            ");
            $stmt->execute(['year' => $year]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $months = [];
            $sales = array_fill(0, 12, 0);

            foreach ($result as $row) {
                $sales[$row['month_num'] - 1] = $row['sales'];
            }

            // Generate labels as full date (e.g., "January 2024")
            foreach (range(1, 12) as $monthNum) {
                $months[] = date('F Y', strtotime("$year-$monthNum-01"));
            }

            $data = ['labels' => $months, 'sales' => $sales];
            break;
    }
} elseif ($startDate && $endDate) {
    // Prepare statement to fetch sales data in the given date range
    $stmt = $connection->prepare("
        SELECT 
            DATE(orders.created_at) AS date, 
            SUM(order_items.quantity * order_items.price) AS total_sales 
        FROM orders 
        JOIN order_items ON orders.id = order_items.order_id
        WHERE DATE(orders.created_at) BETWEEN :start_date AND :end_date
        GROUP BY DATE(orders.created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    
    // Fetch sales data
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize labels and sales arrays with all dates within the range
    $period = new DatePeriod(
        new DateTime(datetime: $startDate),
        new DateInterval('P1D'), // 1 day interval
        (new DateTime($endDate))->modify('+1 day') // Include end date
    );

    $labels = [];
    $sales = [];
    foreach ($period as $date) {
        $formattedDate = $date->format('Y-m-d');
        $labels[] = $formattedDate;
        $sales[$formattedDate] = 0; // Initialize with 0 sales for each date
    }

    // Populate sales data for dates that have records
    foreach ($salesData as $row) {
        $sales[$row['date']] = $row['total_sales'];
    }

    // Convert sales associative array to an indexed array for JSON output
    $data = ['labels' => $labels, 'sales' => array_values($sales)];
}

echo json_encode($data);
?>
