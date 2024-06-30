<?php
require_once 'includes/db.php';

$columns = ['name', 'category', 'price', 'quantity', 'expiry_date', 'image', 'id'];

// Read request parameters
$limit = $_POST['length'];
$start = $_POST['start'];
$order = $columns[$_POST['order'][0]['column']];
$dir = $_POST['order'][0]['dir'];
$search = $_POST['search']['value'];

// Build query
$sql = "SELECT * FROM medicines WHERE 1=1";
if (!empty($search)) {
  $sql .= " AND (name LIKE '%$search%' OR category LIKE '%$search%' OR price LIKE '%$search%' OR quantity LIKE '%$search%' OR expiry_date LIKE '%$search%')";
}
$totalFiltered = $conn->query($sql)->num_rows;
$sql .= " ORDER BY $order $dir LIMIT $start, $limit";

$query = $conn->query($sql);

// Fetch data
$data = [];
while ($row = $query->fetch_assoc()) {
  $data[] = $row;
}

// Prepare response
$response = [
  "draw" => intval($_POST['draw']),
  "recordsTotal" => intval($conn->query("SELECT COUNT(*) FROM medicines")->fetch_row()[0]),
  "recordsFiltered" => intval($totalFiltered),
  "data" => $data
];

// Send JSON response
echo json_encode($response);

$conn->close();
