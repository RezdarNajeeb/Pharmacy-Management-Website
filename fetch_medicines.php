<?php
require_once 'includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

// DataTables server-side processing logic
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$search_value = $_POST['search']['value'];

// Base query
$query = "SELECT * FROM medicines";
$query_count = "SELECT COUNT(*) AS total FROM medicines";
$params = [];

// Total records in the database
$total_records_query = $conn->query($query_count);
$total_records = $total_records_query->fetch_assoc()['total'];

// Total filtered records after applying search
$filtered_records = $total_records; // Initialize with total records

if (!empty($search_value)) {
  $query .= " WHERE name LIKE ? OR category LIKE ?";
  $query_count .= " WHERE name LIKE ? OR category LIKE ?";
  $params[] = '%' . $search_value . '%';
  $params[] = '%' . $search_value . '%';

  // Count filtered records
  $stmt_count = $conn->prepare($query_count);
  $stmt_count->bind_param(str_repeat('s', count($params)), ...$params);
  $stmt_count->execute();
  $filtered_records = $stmt_count->get_result()->fetch_assoc()['total'];
  $stmt_count->close();
}

// Order by
$query .= " ORDER BY name ASC";

// Limit results for pagination
$query .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $length;

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

$stmt->close();

// Prepare response
$response = [
  "draw" => intval($draw),
  "recordsTotal" => $total_records, // Total number of records in the database
  "recordsFiltered" => $filtered_records, // Total number of records after filtering
  "data" => $data // Data rows to display in the DataTable
];

echo json_encode($response);
