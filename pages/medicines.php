<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions
require_once '../includes/db.php';
require_once '../modules/utilities/log_user_activity.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Handle form submission for adding a medicine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medicine'])) {
  $currency = $_POST['currency'];
  $exchange_rate = floatval($_POST['exchange_rate']);
  $name = $_POST['name'];
  $category = $_POST['category'];
  $cost_price = $_POST['cost_price'];
  $selling_price = $_POST['selling_price'];
  $quantity = $_POST['quantity'];
  $expiry_date = $_POST['expiry_date'];
  $barcode = $_POST['barcode'];

  if ($barcode == '') {
    $barcode = null;
  }

  if ($expiry_date == '' || $expiry_date == '0000-00-00') {
    $expiry_date = null;
  }

  // Handle image upload
  $image = null;
  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($image);

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $_SESSION['messages'][] = [
        'type' => 'error',
        'message' => 'هەڵەیەک ڕویدا لە بارکردنی وێنە.'
      ];

      // Redirect to the same page to show the error message
      header("Location: medicines.php");
      exit();
    }
  }

  $stmt = $conn->prepare("INSERT INTO medicines (name, category, cost_price, selling_price, currency, quantity, expiry_date, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssddsisss", $name, $category, $cost_price, $selling_price, $currency, $quantity, $expiry_date, $barcode, $image);

  try {
    if ($stmt->execute() === TRUE) {
      // Log the user activity
      logUserActivity("دەرمانێکی زیادکرد بە ناوی $name.");

      $_SESSION['messages'][] = [
        'type' => 'success',
        'message' => 'دەرمانێکی نوێ زیادکرا.'
      ];
    }
  } catch (mysqli_sql_exception $e) {
    // Log the error
    error_log("Error adding medicine: " . $e->getMessage());

    $_SESSION['messages'][] = [
      'type' => 'error',
      'message' => "هەڵەیەک ڕویدا لە زیادکردنی دەرمان: " . $e->getMessage()
    ];

    // Redirect to the same page and delete the history to prevent resubmission
    header("Location: medicines.php");
    exit();
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>دەرمانەکان</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php
  require_once '../includes/header.php';
  require_once '../includes/messages.php';
  ?>

  <div class="medicines-container">

    <div class="right-side-container">
      <h2 class="title">زیادکردنی دەرمان</h2>
      <form action="medicines.php" id="add-medicine-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="currency" value="USD">
        <input type="hidden" name="exchange_rate" value="1450">

        <div>
          <label for="name">ناوی دەرمان</label>
          <input type="text" class="field" name="name" id="name" required>
        </div>

        <div>
          <label for="category">جۆر</label>
          <input type="text" class="field" name="category" id="category" required>
        </div>

        <div>
          <label for="cost_price">نرخی کڕین</label>
          <input type="number" class="field" name="cost_price" min="0" step="0.01" id="cost_price" required>
        </div>

        <div>
          <label for="selling_price">نرخی فرۆشتن</label>
          <input type="number" class="field" name="selling_price" min="0" step="0.01" id="selling_price" required>
        </div>

        <div>
          <label for="quantity">بڕ</label>
          <input type="number" class="field" name="quantity" id="quantity" min="1" required>
        </div>

        <div>
          <label for="expiry_date">بەسەرچوونی</label>
          <input type="date" class="field" name="expiry_date" id="expiry_date">
        </div>

        <div class="file-upload">
          <input type="file" name="image" id="image-input" class="file-input" accept="image/jpg, image/jpeg, image/png">
          <label for="image-input" class="light-blue-btn file-choose-btn">وێنەیەک هەڵبژێرە</label>
          <span id="image-name" class="file-name">هیچ وێنەیەک هەڵنەبژێردراوە</span>
        </div>

        <div>
          <label for="barcode">بارکۆد</label>
          <input type="text" class="field" name="barcode" id="barcode">
        </div>

        <button type="submit" class="light-green-btn custom-font" name="add_medicine">زیادکردنی دەرمان</button>
      </form>
    </div>

    <div class="left-side-container">
      <table id="medicines-table" class="display">
        <thead>
          <tr>
            <th>#</th>
            <th>وێنە</th>
            <th>ناو</th>
            <th>جۆر</th>
            <th>نرخی کڕین</th>
            <th>نرخی فرۆشتن</th>
            <th>بڕ</th>
            <th>بەسەرچوونی</th>
            <th>بارکۆد</th>
            <th>کردار</th>
          </tr>
        </thead>
        <tbody>
          <!-- Data will be fetched by DataTables via AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Edit Medicine Form Modal -->
  <div id="edit-medicine-modal" class="modal">
    <div class="modal-content">
      <i class="close fas fa-times" onclick="closeEditMedicineModal()"></i>
      <h2 class="title">نوێکردنەوەی دەرمان</h2>
      <form id="edit-medicine-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="existing_image" id="existing-image">
        <input type="hidden" name="currency" value="USD">
        <input type="hidden" name="exchange_rate" value="1450">

        <div class="current-img-cont">
          <img id="current-img" src="" alt="Medicine Image">
          <label for="edit-image" class="edit-icon"><i class="fa-regular fa-pen-to-square"></i></label>
          <input type="file" id="edit-image" name="image" accept="image/jpg, image/jpeg, image/png" class="file-input">
        </div>

        <div>
          <label for="edit-name">ناوی دەرمان</label>
          <input type="text" name="name" id="edit-name" class="field" required>
        </div>

        <div>
          <label for="edit-category">جۆر</label>
          <input type="text" name="category" id="edit-category" class="field" required>
        </div>

        <div>
          <label for="edit-cost_price">نرخی کڕین</label>
          <input type="number" name="cost_price" id="edit-cost_price" min="0" step="0.01" class="field" required>
        </div>

        <div>
          <label for="edit-selling_price">نرخی فرۆشتن</label>
          <input type="number" name="selling_price" id="edit-selling_price" min="0" step="0.01" class="field" required>
        </div>

        <div>
          <label for="edit-quantity">بڕ</label>
          <input type="number" name="quantity" min="1" id="edit-quantity" class="field" required>
        </div>

        <div>
          <label for="edit-expiry_date">بەسەرچوونی</label>
          <input type="date" name="expiry_date" id="edit-expiry_date" class="field">
        </div>

        <div>
          <label for="edit-barcode">بارکۆد</label>
          <input type="text" name="barcode" id="edit-barcode" class="field">
        </div>

        <button type="submit" class="light-yellow-btn custom-font">نوێکردنەوە</button>
      </form>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(document).ready(function() {
      $('#medicines-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "../modules/medicines/fetch_medicines.php",
          "type": "POST",
          "error": function(xhr, status, error) {
            console.error('Error:', error); // Debugging: log the error
            alert('Failed to load the medicines. Please refresh the page.');
          },
        },
        "language": {
          "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Kurdish.json",
          "emptyTable": "هیچ دەرمانێک لە سیستەمەکەدا نییە."
        },
        "columnDefs": [{
            "orderable": false,
            "targets": [0, 1, 8, 9]
          }, // Disable sorting for specific columns
          {
            "visible": false,
            "targets": [8] // Hide the barcode column
          }
        ],
        "order": [
          [2, 'asc']
        ], // Default sorting by the third column (name) in ascending order
        "columns": [{
            "data": null, // Use null since we will render custom data
            "render": function(data, type, row, meta) {
              return meta.row + 1; // Display row number starting from 1
            }
          },
          {
            "data": "image",
            "render": function(data) {
              const imageUrl = data ? `../uploads/${data}` : '../assets/images/no-image.avif';
              return `<img src="${imageUrl}" alt="Medicine Image" class="image">`;
            }
          },
          {
            "data": "name"
          },
          {
            "data": "category"
          },
          {
            "data": "cost_price",
            "render": function(data, type, row) {
              var costPrice = row['cost_price'];
              var currency = row['currency'];

              if (currency === 'USD') {
                return parseFloat(costPrice).toFixed(2) + ' $<br><br>' + (costPrice * <?= $exchange_rate ?>).toFixed(0) + ' IQD';
              } else {
                return (costPrice / <?= $exchange_rate ?>).toFixed(2) + ' $<br><br>' + parseFloat(costPrice).toFixed(0) + ' IQD';
              }
            }
          },
          {
            "data": "selling_price",
            "render": function(data, type, row) {
              var sellingPrice = row['selling_price'];
              var currency = row['currency'];

              if (currency === 'USD') {
                return parseFloat(sellingPrice).toFixed(2) + ' $<br><br>' + (sellingPrice * <?= $exchange_rate ?>).toFixed(0) + ' IQD';
              } else {
                return (sellingPrice / <?= $exchange_rate ?>).toFixed(2) + ' $<br><br>' + parseFloat(sellingPrice).toFixed(0) + ' IQD';
              }
            }
          },
          {
            "data": "quantity",
          },
          {
            "data": "expiry_date",
            "render": function(data) {
              return data === null || data === '0000-00-00' || data === '' ? 'بەسەرچوونی نییە' : data;
            }
          },
          {
            "data": "barcode"
          }, // Ensure the barcode column is included
          {
            "data": "id",
            "render": function(data) {
              return `
              <div class="actions">
                <button type="button" class="light-yellow-btn" onclick="showEditMedicineModal(${data})"><i class="fa-regular fa-pen-to-square"></i></button>
                <form method="post" id="delete-medicine-form" onsubmit="deleteMedicine(${data});">
                  <button class="red-btn"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
              `;
            }
          }
        ],
        "initComplete": function(settings, json) {
          // Focus on the search input field after DataTables has fully loaded
          $('.dataTables_filter input').focus();
          $('#medicines-table_wrapper').prepend('<h2 class="title">دەرمانەکان</h2>');
        },
        "scrollY": "500px", // Set vertical scrollable area height
        "scrollX": true, // Enable horizontal scrolling
        "scrollCollapse": true, // Collapse the table to fit the content if less than set height
        "scrollXInner": "100%", // Allow horizontal scrolling to extend beyond the table width
      });
    });

    function showEditMedicineModal(id) {
      // Fetch the medicine details and fill the form
      $.ajax({
        url: '../modules/medicines/edit_medicine.php',
        type: 'POST',
        data: {
          id: id
        },
        dataType: 'json',
        success: function(response) {
          const medicine = response.medicine;
          $('#edit-id').val(medicine.id);
          $('#edit-name').val(medicine.name);
          if (medicine.image) {
            $('#current-img').attr('src', `../uploads/${medicine.image}`);
          } else {
            $('#current-img').attr('src', '../assets/images/no-image.avif');
          }
          $('#edit-category').val(medicine.category);
          $('#edit-cost_price').val(medicine.cost_price);
          $('#edit-selling_price').val(medicine.selling_price);
          $('#edit-quantity').val(medicine.quantity);
          $('#edit-expiry_date').val(medicine.expiry_date);
          $('#edit-barcode').val(medicine.barcode);
          $('#existing-image').val(medicine.image);
          $('#edit-medicine-modal').css('visibility', 'visible');
        }
      });
    }

    function closeEditMedicineModal() {
      $('#edit-medicine-modal').css('visibility', 'hidden');
    }

    // update medicine in the scripts.js

    function deleteMedicine(id) {
      if (confirm('دڵنیایت کە دەتەوێت ئەم دەرمانە بسڕیتەوە؟')) {
        $.ajax({
          url: '../modules/medicines/delete_medicine.php',
          type: 'POST',
          data: {
            id: id
          },
          success: function(response) {
            location.reload();
          },
          error: function(xhr, status, error) {
            console.error('Error:', error); // Debugging: log the error
            alert('Failed to delete the medicine. Please try again.');
          }
        });
      }
    }
  </script>
</body>

</html>