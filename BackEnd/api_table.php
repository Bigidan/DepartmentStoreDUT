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

$selectedNavItem = $_GET['selectedNavItem'];

switch ($selectedNavItem) {
        case 'products':
            $columns = [
                ['name' => 'product_name', 'type' => 'string'],
                ['name' => 'prime_cost', 'type' => 'string'],
                ['name' => 'wholesale_price', 'type' => 'string'],
                ['name' => 'retail_price', 'type' => 'string'],
                ['name' => 'dollar_cost', 'type' => 'string'],
                ['name' => 'uah_cost', 'type' => 'string'],
                ['name' => 'product_categories_category_id', 'type' => 'dropdown', 'options' => getCategoryOptions($conn)]
            ];
            break;
        case 'categories':
            $columns = [
                ['name' => 'category_name', 'type' => 'string'],
                ['name' => 'measurement', 'type' => 'string']
            ];
            break;
        case 'providers':
            $columns = [
                ['name' => 'provider_name', 'type' => 'string']
            ];
            break;
        case 'storage':
            $columns = [
                ['name' => 'trans_id', 'type' => 'dropdown', 'options' => getTransportationOptions($conn)]
            ];
            break;
        case 'tracking':
            $columns = [
                ['name' => 'vat', 'type' => 'string'],
                ['name' => 'customs', 'type' => 'string'],
                ['name' => 'assignment_expenses', 'type' => 'string'],
                ['name' => 'delivering_date', 'type' => 'string'],
                ['name' => 'provider_id', 'type' => 'dropdown', 'options' => getProviderOptions($conn)],
                ['name' => 'product_id', 'type' => 'dropdown', 'options' => getProductOptions($conn)],
                ['name' => 'quantity', 'type' => 'string']
            ];
            break;
    }
$response['columns'] = $columns;

function getCategoryOptions($conn) {
    $sql = "SELECT category_id, category_name FROM product_categories";
    $result = $conn->query($sql);
    $options = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options[] = $row['category_name'];
        }
    }
    return $options;
}

function getProductOptions($conn) {
    $sql = "SELECT product_id, product_name FROM products";
    $result = $conn->query($sql);
    $options = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options[] = $row['product_name'];
        }
    }
    return $options;
}

function getTransportationOptions($conn) {
    $sql = "SELECT trans_id, CONCAT('Transportation #', trans_id) AS trans_name FROM transportations";
    $result = $conn->query($sql);
    $options = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options[] = $row['trans_name'];
        }
    }
    return $options;
}

function getProviderOptions($conn) {
    $sql = "SELECT provider_id, provider_name FROM providers";
    $result = $conn->query($sql);
    $options = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options[] = $row['provider_name'];
        }
    }
    return $options;
}

$conn->close();

echo json_encode($response);