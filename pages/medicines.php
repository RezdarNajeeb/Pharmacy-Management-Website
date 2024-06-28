<?php
require_once '../includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

// Handle form submission for adding a medicine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medicine'])) {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $expiry_date = $_POST['expiry_date'];

  // Handle image upload
  $image = $_FILES['image']['name'];
  $target_dir = "../uploads/";
  $target_file = $target_dir . basename($image);

  if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
    $stmt = $conn->prepare("INSERT INTO medicines (name, category, price, quantity, expiry_date, image) VALUES (?, ?, ?, ?, ?, ?)");
    echo $expiry_date;
    $stmt->bind_param("ssdiss", $name, $category, $price, $quantity, $expiry_date, $image);

    if ($stmt->execute()) {
      echo "Medicine added successfully.";
    } else {
      echo "Error: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "Error uploading image.";
  }
}

// Fetch medicines from the database
$stmt = $conn->prepare("SELECT * FROM medicines");
$stmt->execute();
$medicines = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>دەرمانەکان</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />

</head>

<body>
  <?php include '../includes/header.php'; ?>

  <div class="medicines-container">

    <div class="right-side-container">
      <div class="info-content">
        <h1>
          دەرمانخانەی سەردەشت
        </h1>
        <img src="../assets/images/logo.jpg" alt="logo">
      </div>

      <div class="add-medicine">
        <h2 class="title">زیادکردنی دەرمان</h2>
        <form id="add-medicine-form" method="post" enctype="multipart/form-data">
          <input type="text" name="name" placeholder="ناوی دەرمان" required>
          <input type="text" name="category" placeholder="پۆل" required>
          <input type="number" name="price" min="0" placeholder="نرخ" required>
          <input type="number" name="quantity" min="0" placeholder="بڕ" required>
          <input type="date" name="expiry_date" placeholder="بەسەرچوونی" required>
          <input type="file" name="image" accept="image/*" required>
          <button type="submit" name="add_medicine">زیادکردنی دەرمان</button>
        </form>
      </div>
    </div>

    <div class="left-side-container">
      <h2 class="title">دەرمانەکان</h2>
      <table id="medicines-table">
        <thead>
          <th>#</th>
          <th>ناو</th>
          <th>پۆل</th>
          <th>نرخ</th>
          <th>بڕ ($)</th>
          <th>بەسەرچوونی</th>
          <th>وێنە</th>
          <th>کردار</th>
        </thead>
        <tbody>
          <?php foreach ($medicines as $index => $medicine) : ?>
            <tr>
              <td><?php echo $index + 1; ?></td>
              <td><?php echo $medicine['name']; ?></td>
              <td><?php echo $medicine['category']; ?></td>
              <td><?php echo $medicine['price']; ?></td>
              <td><?php echo $medicine['quantity']; ?></td>
              <td><?php echo $medicine['expiry_date']; ?></td>
              <td><img src="../uploads/<?php echo $medicine['image']; ?>" alt="Medicine Image" width="50"></td>
              <td>
                <button type="button" class="edit-button" onclick="showEditMedicineModal()">دەستکاری</button>
                <form action="../delete_medicine.php" method="post" onsubmit="return confirm('Are you sure you want to delete this item?');">
                  <input type="hidden" name="id" value="<?php echo $medicine['id']; ?>">
                  <button type="submit" class="delete-button">سڕینەوە</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Edit Medicine Form Modal -->
  <div id="edit-medicine-modal" class="modal">
    <div class="modal-content">
      <i class="close fas fa-times" onclick="closeEditMedicineModal()"></i>
      <form action="../update_medicine.php" id="edit-medicine-form" method="post" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $medicine['id'] ?>" name="id" id="edit-id">
        <input type="hidden" value="<?php echo $medicine['image'] ?>" name="existing_image" id="existing-image">
        <input type="text" name="name" value="<?php echo $medicine['name'] ?>" id="edit-name" placeholder="ناوی دەرمان" required>
        <input type="text" name="category" value="<?php echo $medicine['category'] ?>" id="edit-category" placeholder="پۆل" required>
        <input type="number" name="price" value="<?php echo $medicine['price'] ?>" id="edit-price" placeholder="نرخ" required>
        <input type="number" name="quantity" value="<?php echo $medicine['quantity'] ?>" id="edit-quantity" placeholder="بڕ" required>
        <input type="date" name="expiry_date" value="<?php echo $medicine['expiry_date'] ?>" id="edit-expiry_date" placeholder="بەسەرچوونی" required>
        <input type="file" name="image" id="edit-image" accept="image/*">
        <button type="submit">نوێکردنەوەی دەرمان</button>
      </form>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $('#medicines-table').DataTable({
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Kurdish.json"
      }
    });

    function showEditMedicineModal() {
      $('#edit-medicine-modal').css('visibility', 'visible');
    }

    function closeEditMedicineModal() {
      $('#edit-medicine-modal').hide();
    }
  </script>
</body>

</html>