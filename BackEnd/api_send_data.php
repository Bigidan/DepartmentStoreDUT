<?php
// Перевірте, чи запит прийшов з дозволеного джерела
$allowedOrigins = array(
    "http://localhost:4200",
    "http://localhost:8000",
    // Додайте інші дозволені домени, якщо потрібно
);

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enactment_proj";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array();

// Обробка POST запиту
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid JSON data."));
    } else {
        // Тут ви можете обробити отримані дані
        // Наприклад, зберегти їх в базу даних

        $content = $data->content;
        $transformedData = $data->transformedData;

        foreach ($transformedData as $record) {
            $mess = insertData($conn, $content, $record);
        }

        http_response_code(200);
        echo json_encode(array("message" => $mess));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}



function insertData($conn, $selectedNavItem, $data) {
    switch ($selectedNavItem) {
        case 'products':
            $category_sql = "SELECT category_id FROM product_categories WHERE category_name = ?";
            $stmt_category = $conn->prepare($category_sql);
            $stmt_category->bind_param("s", $data->product_categories_category_id);
            $stmt_category->execute();
            $result_category = $stmt_category->get_result();

            if ($result_category->num_rows > 0) {
                    $row = $result_category->fetch_assoc();
                    $category_id = $row['category_id'];

                    // Потім виконуємо вставку даних у таблицю products
                    $sql = "INSERT INTO products (product_name, prime_cost, wholesale_price, retail_price, dollar_cost, uah_cost, product_categories_category_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssi", $data->product_name, $data->prime_cost, $data->wholesale_price, $data->retail_price, $data->dollar_cost, $data->uah_cost, $category_id);
                }
            break;
            
        case 'categories':
            $sql = "INSERT INTO product_categories (category_name, measurement)
                    VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $data->category_name, $data->measurement);
            break;
            
        case 'providers':
            $sql = "INSERT INTO providers (provider_name)
                    VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $data->provider_name);
            break;
            
        case 'storage':
            $sql_details = "
                SELECT tt.quantity, tt.product_id, p.dollar_cost 
                FROM trans_type tt
                JOIN transportations t ON tt.trans_id = t.trans_id
                JOIN products p ON tt.product_id = p.product_id
                WHERE t.trans_id = ?
            ";
            $stmt_details = $conn->prepare($sql_details);
            $trans_id = str_replace('Transportation #', '', $data->trans_id);
            $stmt_details->bind_param("i", $trans_id);
            $stmt_details->execute();
            $result_details = $stmt_details->get_result();

            if ($result_details->num_rows > 0) {
                $row = $result_details->fetch_assoc();
                $quantity = $row['quantity'];
                $product_id = $row['product_id'];
                $dollar_rate = $row['dollar_cost'];
            } else {
                return "Error: No matching record found for this trans_id.";
            }

            // Отримуємо поточну дату
            $current_date = date("Y-m-d");

            // Вставка даних у таблицю storage
            $sql_insert = "INSERT INTO storage (quantity, last_update_date, dollar_rate, product_id, trans_id)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("isdii", $quantity, $current_date, $dollar_rate, $product_id, $trans_id);
            break;
            
        case 'tracking':
            $current_date = date("Y-m-d");

            // Вставляємо дані в таблицю transportations
            $sql_transport = "INSERT INTO transportations (vat, customs, assignment_expenses, delivering_date)
                              VALUES (?, ?, ?, ?)";
            $stmt_transport = $conn->prepare($sql_transport);
            $stmt_transport->bind_param("ddds", $data->vat, $data->customs, $data->assignment_expenses, $current_date);
            $stmt_transport->execute();

            // Отримуємо ID останнього вставленого транспорту
            $last_trans_id = $conn->insert_id;

            // Отримуємо provider_id на основі provider_name
            $sql_provider = "SELECT provider_id FROM providers WHERE provider_name = ?";
            $stmt_provider = $conn->prepare($sql_provider);
            $stmt_provider->bind_param("s", $data->provider_id);
            $stmt_provider->execute();
            $result_provider = $stmt_provider->get_result();

            if ($result_provider->num_rows > 0) {
                $row_provider = $result_provider->fetch_assoc();
                $provider_id = $row_provider['provider_id'];
            } else {
                return "Error: No matching provider found for the given provider name.";
            }

            // Отримуємо product_id на основі product_name
            $sql_product = "SELECT product_id FROM products WHERE product_name = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param("s", $data->product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();

            if ($result_product->num_rows > 0) {
                $row_product = $result_product->fetch_assoc();
                $product_id = $row_product['product_id'];
            } else {
                return "Error: No matching product found for the given product name.";
            }

            // Вставляємо дані в таблицю trans_type
            $sql_trans_type = "INSERT INTO trans_type (trans_id, provider_id, product_id, quantity)
                               VALUES (?, ?, ?, ?)";
            $stmt_trans_type = $conn->prepare($sql_trans_type);
            $stmt_trans_type->bind_param("iiii", $last_trans_id, $provider_id, $product_id, $data->quantity);
            $stmt_trans_type->execute();

            return "New tracking record created successfully with transportation ID: " . $last_trans_id;
            
        default:
            return "Invalid selection";
    }
    
    if ($stmt->execute()) {
        return "New record created successfully";
    } else {
        return "Error: " . $stmt->error;
    }
}

function insertIntoStorage($conn, $trans_id) {
    // Отримуємо дані з таблиці trans_type, використовуючи trans_id з таблиці transportations
    

    if ($stmt_insert->execute()) {
        return "New storage record created successfully";
    } else {
        return "Error: " . $stmt_insert->error;
    }
}



?>