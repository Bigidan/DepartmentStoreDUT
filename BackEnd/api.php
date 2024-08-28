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

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'filters';
$selectedNavItem = isset($_GET['tab']) ? $_GET['tab'] : 'products';

$filter_first_a = isset($_GET['filter_first_a']) ? $_GET['filter_first_a'] : null;
$filter_second_a = isset($_GET['filter_second_a']) ? $_GET['filter_second_a'] : null;

if ($mode == 'filters') {
    $sql_2 = "";
    switch ($selectedNavItem) {
        case 'tracking':
            $sql = "SELECT tt.product_id AS 'id',
                    COUNT(DISTINCT t.trans_id) AS 'counts',
                    p.product_name AS 'name'
                    FROM transportations t
                    JOIN trans_type tt ON t.trans_id = tt.trans_id
                    JOIN products p ON tt.product_id = p.product_id
                    GROUP BY tt.product_id";
            $sql_2 = "SELECT tt.provider_id AS 'id',
                    COUNT(DISTINCT t.trans_id) AS 'counts',
                    pr.provider_name AS 'name'
                    FROM transportations t
                    JOIN trans_type tt ON t.trans_id = tt.trans_id
                    JOIN providers pr ON tt.provider_id = pr.provider_id
                    GROUP BY tt.provider_id";
            break;
        case 'providers':
            $sql = "SELECT p.product_id AS 'id',
            COUNT(DISTINCT p.product_id) AS 'counts',
            p.product_name AS 'name'
            FROM providers pr
            JOIN trans_type tt ON pr.provider_id = tt.provider_id
            JOIN products p ON tt.product_id = p.product_id
            GROUP BY pr.provider_id";
            break;
        case 'products':
            $sql = "SELECT pc.category_name AS 'name',
                    pc.category_id AS 'id',
                    COUNT(p.product_id) AS 'counts'
                    FROM product_categories pc
                    LEFT JOIN products p ON p.product_categories_category_id = pc.category_id
                    GROUP BY pc.category_id";
            break;
        case 'storage':
            $sql = "SELECT p.product_id AS 'id',
                    COUNT(DISTINCT s.store_id) AS 'counts',
                    p.product_name AS 'name'
                    FROM products p
                    JOIN storage s ON p.product_id = s.product_id
                    GROUP BY p.product_id";
            $sql_2 = "SELECT pr.provider_id AS 'id',
                    COUNT(DISTINCT s.store_id) AS 'counts',
                    pr.provider_name AS 'name'
                    FROM storage s
                    JOIN trans_type tt ON s.trans_id = tt.trans_id
                    JOIN providers pr ON tt.provider_id = pr.provider_id
                    GROUP BY pr.provider_id";
            break;
        case 'categories':
            $sql = "SELECT p.product_name AS 'name', p.product_id AS 'id', '-' AS 'counts' FROM product_categories pc LEFT JOIN products p ON p.product_categories_category_id = pc.category_id";
            break;
        default:
            break;
    }
    $result = $conn->query($sql);
    $filters = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $filters[] = $row;
        }
    }
    $response['filter_1'] = $filters;
    if ($sql_2 != "") {
        $result = $conn->query($sql_2);
        $items = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $response['filter_2'] = $items;
    }
} elseif ($mode == 'items') {
    $where_conditions = [];

    switch ($selectedNavItem) {
        case 'tracking':
            if (!empty($filter_first_a)) {
                $filter_first_a = array_map('intval', $filter_first_a);
                $where_conditions[] = "p.product_id IN (" . implode(',', $filter_first_a) . ")";
            }

            if (!empty($filter_second_a)) {
                $filter_second_a = array_map('intval', $filter_second_a);
                $where_conditions[] = "pr.provider_id IN (" . implode(',', $filter_second_a) . ")";
            }

            $sql = "SELECT DISTINCT t.trans_id AS 'id', 
                    p.product_name AS 'name', 
                    CONCAT('#Кількість: ', tt.quantity, '@Постачальник: ', pr.provider_name, '#Відправлено: ', t.delivering_date, '@') AS 'value',
                    s.store_id IS NOT NULL AS 'status'
                    FROM transportations t
                    JOIN trans_type tt ON t.trans_id = tt.trans_id
                    JOIN products p ON tt.product_id = p.product_id
                    JOIN providers pr ON tt.provider_id = pr.provider_id
                    LEFT JOIN storage s ON t.trans_id = s.trans_id";

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            break;
        case 'providers':
            if (!empty($filter_first_a)) {
                $filter_first_a = array_map('intval', $filter_first_a);
                $where_conditions[] = "pr.provider_id IN (" . implode(',', $filter_first_a) . ")";
            }

            $sql = "SELECT DISTINCT pr.provider_name AS 'name',
                    pr.provider_id AS 'id', 
                    CONCAT('Постачає товарів: ', COUNT(DISTINCT p.product_id)) AS 'value'
                    FROM providers pr
                    JOIN trans_type tt ON pr.provider_id = tt.provider_id
                    JOIN products p ON tt.product_id = p.product_id";

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            $sql .= " GROUP BY pr.provider_name, pr.provider_id";

            break;
        case 'products':
            if (!empty($filter_first_a)) {
                $filter_first_a = array_map('intval', $filter_first_a);
                $where_conditions[] = "pc.category_id IN (" . implode(',', $filter_first_a) . ")";
            }

            $sql = "SELECT DISTINCT p.product_name AS 'name',
                    p.product_id AS 'id',
                    CONCAT('#Ціна: ', p.uah_cost, '₴ / ', p.dollar_cost, '$@Собівартість: ', p.prime_cost, '#Оптова ціна: ', p.wholesale_price, '@') AS 'value'
                    FROM products p
                    JOIN product_categories pc ON p.product_categories_category_id = pc.category_id";

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            break;
        case 'storage':
            if (!empty($filter_first_a)) {
                $filter_first_a = array_map('intval', $filter_first_a);
                $where_conditions[] = "p.product_id IN (" . implode(',', $filter_first_a) . ")";
            }

            if (!empty($filter_second_a)) {
                $filter_second_a = array_map('intval', $filter_second_a);
                $where_conditions[] = "pr.provider_id IN (" . implode(',', $filter_second_a) . ")";
            }

            $sql = "SELECT DISTINCT p.product_name AS 'name',
                    s.store_id AS 'id',
                    CONCAT('#Кількість: ', s.quantity, '@Ціна: ', p.uah_cost, '₴ / ', p.dollar_cost, '$#Курс долара: ', s.dollar_rate, '@Оптова ціна: ', p.wholesale_price, '@Остання доставка: ', s.last_update_date) AS 'value'
                    FROM storage s
                    JOIN products p ON s.product_id = p.product_id
                    JOIN trans_type tt ON s.trans_id = tt.trans_id
                    JOIN providers pr ON tt.provider_id = pr.provider_id";

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            break;
        case 'categories':
            if (!empty($filter_first_a)) {
                $filter_first_a = array_map('intval', $filter_first_a);
                $where_conditions[] = "p.product_id IN (" . implode(',', $filter_first_a) . ")";
            }

            $sql = "SELECT DISTINCT pc.category_name AS 'name',
                    pc.category_id AS 'id',
                    CONCAT('Має товарів: ', COUNT(p.product_id)) AS 'value'
                    FROM product_categories pc
                    LEFT JOIN products p ON p.product_categories_category_id = pc.category_id";

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            $sql .= " GROUP BY pc.category_id";

            break;

         default:
             break;
    }

    
    $result = $conn->query($sql);
    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $response['items'] = $items;
}

$conn->close();

echo json_encode($response);