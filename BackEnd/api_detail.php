<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enactment_proj";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array();

$selectedItemId = isset($_GET['selectedItemId']) ? $_GET['selectedItemId'] : die();
$selectedNavItem = $_GET['selectedNavItem'];

switch ($selectedNavItem) {
    case 'tracking':
        $sqlDetails = "SELECT t.*, p.*, tt.quantity,
                s.store_id IS NOT NULL AS 'status'
                FROM transportations t
                JOIN trans_type tt ON t.trans_id = tt.trans_id
                JOIN products p ON tt.product_id = p.product_id
                LEFT JOIN storage s ON t.trans_id = s.trans_id AND p.product_id = s.product_id
                WHERE t.trans_id = '$selectedItemId'
                GROUP BY t.trans_id";
        
        $sqlItems = "SELECT t.*, p.*, tt.quantity
                     FROM transportations t
                     JOIN trans_type tt ON t.trans_id = tt.trans_id
                     JOIN products p ON tt.product_id = p.product_id
                     WHERE t.trans_id = '$selectedItemId'";
        
        $sqlProvider = "SELECT DISTINCT pr.provider_id, pr.provider_name,
                     COUNT(DISTINCT tt.trans_id) AS delivery_count,
                     SUM(tt.quantity) AS total_delivered
                        FROM transportations t
                        JOIN trans_type tt ON t.trans_id = tt.trans_id
                        JOIN providers pr ON tt.provider_id = pr.provider_id
                        WHERE t.trans_id = '$selectedItemId'
                        GROUP BY pr.provider_id";
        break;

    case 'providers':
    $sqlProvider = "SELECT pr.*, 
            (SELECT COUNT(DISTINCT tt.trans_id)
             FROM trans_type tt
             WHERE tt.provider_id = pr.provider_id) AS total_transportations,
            (SELECT SUM(tt.quantity)
             FROM trans_type tt 
             WHERE tt.provider_id = pr.provider_id) AS total_delivered
            FROM providers pr
            WHERE pr.provider_id = '$selectedItemId'";
    
    $sqlItems = "SELECT p.*, 
            (SELECT COUNT(DISTINCT tt.trans_id)
             FROM trans_type tt
             WHERE tt.provider_id = '$selectedItemId' AND tt.product_id = p.product_id) AS delivery_count,
            (SELECT SUM(tt.quantity)
             FROM trans_type tt
             WHERE tt.provider_id = '$selectedItemId' AND tt.product_id = p.product_id) AS total_delivered
            FROM products p
            JOIN trans_type tt ON p.product_id = tt.product_id
            WHERE tt.provider_id = '$selectedItemId'
            GROUP BY p.product_id";
    
    $sqlDetails = "SELECT t.trans_id, t.delivering_date, tt.quantity, 
                            s.store_id IS NOT NULL AS 'status',
                           p.product_name, s.quantity AS storage_quantity
                           FROM transportations t
                           JOIN trans_type tt ON t.trans_id = tt.trans_id
                           JOIN products p ON tt.product_id = p.product_id
                           LEFT JOIN storage s ON t.trans_id = s.trans_id AND tt.product_id = s.product_id
                           WHERE tt.provider_id = '$selectedItemId'
                           ORDER BY t.delivering_date DESC
                           LIMIT 5";
    break;

    case 'products':
    $sqlItems = "SELECT p.*, pc.category_name,
            (SELECT COUNT(DISTINCT tt.trans_id) 
             FROM trans_type tt 
             WHERE tt.product_id = p.product_id) AS total_transportations,
            (SELECT SUM(tt.quantity) 
             FROM trans_type tt 
             WHERE tt.product_id = p.product_id) AS total_quantity,
            (SELECT SUM(s.quantity) 
             FROM storage s 
             WHERE s.product_id = p.product_id) AS storage_quantity
            FROM products p
            JOIN product_categories pc ON p.product_categories_category_id = pc.category_id
            WHERE p.product_id = '$selectedItemId'";
    
    $sqlProvider = "SELECT DISTINCT pr.provider_id, pr.provider_name,
                     COUNT(DISTINCT tt.trans_id) AS delivery_count,
                     SUM(tt.quantity) AS total_delivered
                     FROM providers pr
                     JOIN trans_type tt ON pr.provider_id = tt.provider_id
                     WHERE tt.product_id = '$selectedItemId'
                     GROUP BY pr.provider_id";
    
    $sqlDetails = "SELECT t.trans_id, t.delivering_date, tt.quantity,
                            s.store_id IS NOT NULL AS 'status',
                           pr.provider_name, s.quantity AS storage_quantity
                           FROM transportations t
                           JOIN trans_type tt ON t.trans_id = tt.trans_id
                           JOIN providers pr ON tt.provider_id = pr.provider_id
                           LEFT JOIN storage s ON t.trans_id = s.trans_id AND tt.product_id = s.product_id
                           WHERE tt.product_id = '$selectedItemId'
                           ORDER BY t.delivering_date DESC
                           LIMIT 5";
    break;

    case 'storage':
    $sqlDetails = "SELECT t.trans_id, t.delivering_date, tt.quantity, 
                   p.product_name, pr.provider_name, s.quantity AS storage_quantity
                   FROM transportations t
                   JOIN trans_type tt ON t.trans_id = tt.trans_id
                   JOIN products p ON tt.product_id = p.product_id
                   JOIN providers pr ON tt.provider_id = pr.provider_id
                   JOIN storage s ON t.trans_id = s.trans_id AND p.product_id = s.product_id
                   WHERE p.product_id = '$selectedItemId'
                   ORDER BY t.delivering_date DESC";
    
    $sqlItems = "SELECT p.product_name, s.store_id AS id,
                 CONCAT('#Кількість: ', s.quantity, '@Ціна: ', p.uah_cost, '₴ / ', p.dollar_cost, '$#Курс долара: ', s.dollar_rate, '@Оптова ціна: ', p.wholesale_price, '@Остання доставка: ', s.last_update_date) AS 'value'
                 FROM storage s
                 JOIN products p ON s.product_id = p.product_id
                 JOIN trans_type tt ON s.trans_id = tt.trans_id
                 WHERE p.product_id = '$selectedItemId'
                 ORDER BY s.last_update_date DESC";
    
    $sqlProvider = "SELECT pr.provider_name, pr.provider_id
                    FROM providers pr
                    JOIN trans_type tt ON pr.provider_id = tt.provider_id
                    WHERE tt.product_id = '$selectedItemId'
                    LIMIT 1";
    break;
    case 'categories':
    $sqlDetails = "SELECT pc.*,
            (SELECT COUNT(DISTINCT p.product_id)
             FROM products p
             WHERE p.product_categories_category_id = pc.category_id) AS total_products
            FROM product_categories pc
            WHERE pc.category_id = '$selectedItemId'";

    $sqlProvider = "SELECT DISTINCT pr.provider_id, pr.provider_name,
                     COUNT(DISTINCT tt.trans_id) AS delivery_count,
                     SUM(tt.quantity) AS total_delivered
                     FROM providers pr
                     JOIN trans_type tt ON pr.provider_id = tt.provider_id
                     JOIN products p ON tt.product_id = p.product_id
                     WHERE p.product_categories_category_id = '$selectedItemId'
                     GROUP BY pr.provider_id";

    $sqlItems = "SELECT p.product_id, p.product_name, p.prime_cost, p.wholesale_price, p.retail_price, p.dollar_cost, p.uah_cost
                 FROM products p
                 WHERE p.product_categories_category_id = '$selectedItemId'";
    break;
    default:
        break;
}
$result = $conn->query($sqlDetails);

$filters = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $filters[] = $row;
    }
}
$response['details'] = $filters;

$resultItems = $conn->query($sqlItems);
$items = array();
if ($resultItems->num_rows > 0) {
    while($row = $resultItems->fetch_assoc()) {
        $items[] = $row;
    }
}
$response['items'] = $items;


$resultProviders = $conn->query($sqlProvider);
$prov = array();
if ($resultProviders->num_rows > 0) {
    while($row = $resultProviders->fetch_assoc()) {
        $prov[] = $row;
    }
}
$response['provider'] = $prov;

$conn->close();

echo json_encode($response);