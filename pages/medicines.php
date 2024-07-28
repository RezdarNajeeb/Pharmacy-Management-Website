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

  // convert cost price and selling price to USD if currency is USD
  if ($currency == 'USD') {
    $cost_price = $cost_price * $exchange_rate;
    $selling_price = $selling_price * $exchange_rate;
  }

  // Handle image upload
  $image = $_FILES['image']['name'];
  $target_dir = "../uploads/";
  $target_file = $target_dir . basename($image);

  if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
    $stmt = $conn->prepare("INSERT INTO medicines (name, category, cost_price, selling_price, quantity, expiry_date, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddisss", $name, $category, $cost_price, $selling_price, $quantity, $expiry_date, $barcode, $image);

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
      // change the language of $e->getMessage() to Kurdish
      error_log("Error adding medicine: " . $e->getMessage());

      $_SESSION['messages'][] = [
        'type' => 'error',
        'message' => 'هەڵەیەک ڕویدا لە زیادکردنی دەرمان.'
      ];

      // Redirect to the same page to show the error message
      header("Location: medicines.php");
      exit();
    }

    $stmt->close();
  } else {
    $_SESSION['messages'][] = [
      'type' => 'error',
      'message' => 'هەڵەیەک ڕویدا لە بارکردنی وێنە.'
    ];
  }
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
      <form id="add-medicine-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="currency" value="USD">
        <input type="hidden" name="exchange_rate" value="1450">
        <input type="text" name="name" placeholder="ناوی دەرمان" required>
        <input type="text" name="category" placeholder="جۆر" required>
        <input type="number" name="cost_price" min="0" placeholder="نرخی کڕین" required>
        <input type="number" name="selling_price" min="0" placeholder="نرخی فرۆشتن" required>
        <input type="number" name="quantity" min="0" placeholder="بڕ" required>
        <input type="date" name="expiry_date" placeholder="بەسەرچوونی" required>
        <div class="file-upload">
          <input type="file" name="image" id="image-input" class="file-input" accept="image/*">
          <label for="image-input" class="light-blue-btn file-choose-btn">وێنەیەک هەڵبژێرە</label>
          <span id="image-name" class="file-name">هیچ وێنەیەک هەڵنەبژێردراوە</span>
        </div>
        <input type="text" name="barcode" id="barcode" placeholder="بارکۆد" required>
        <button type="submit" class="light-green-btn" name="add_medicine">زیادکردنی دەرمان</button>
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
          <img id="current-img" src="" alt="Current Image">
          <label for="edit-image" class="edit-icon"><i class="fa-regular fa-pen-to-square"></i></label>
          <input type="file" id="edit-image" name="image" accept="image/*" style="display: none;">
        </div>
        <input type="text" name="name" id="edit-name" placeholder="ناوی دەرمان" required>
        <input type="text" name="category" id="edit-category" placeholder="پۆل" required>
        <input type="number" name="cost_price" id="edit-cost_price" placeholder="نرخی کڕین" required>
        <input type="number" name="selling_price" id="edit-selling_price" placeholder="نرخی فرۆشتن" required>
        <input type="number" name="quantity" id="edit-quantity" placeholder="بڕ" required>
        <input type="date" name="expiry_date" id="edit-expiry_date" placeholder="بەسەرچوونی" required>
        <input type="text" name="barcode" id="edit-barcode" placeholder="بارکۆد" required>
        <button type="submit" class="light-blue-btn">نوێکردنەوە</button>
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
          "type": "POST"
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
              return `<img src="../uploads/${data}" alt="Medicine Image" class="image">`;
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
            "render": function(data) {
              return (data / <?= $exchange_rate ?>).toFixed(2) + ' $<br><br>' + parseFloat(data).toFixed(0) + ' د.ع';
            }
          },
          {
            "data": "selling_price",
            "render": function(data) {
              return (data / <?= $exchange_rate ?>).toFixed(2) + ' $<br><br>' + parseFloat(data).toFixed(0) + ' د.ع';
            }
          },
          {
            "data": "quantity"
          },
          {
            "data": "expiry_date"
          },
          {
            "data": "barcode"
          }, // Ensure the barcode column is included
          {
            "data": "id",
            "render": function(data) {
              return `
              <div class="actions">
                <button type="button" class="light-blue-btn" onclick="showEditMedicineModal(${data})">نوێکردنەوە</button>
                <form method="post" id="delete-medicine-form" onsubmit="deleteMedicine(${data});">
                  <input type="submit" value="سڕینەوە" class="red-btn"></input>
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
        "scrollY": "330px", // Set vertical scrollable area height
        "scrollX": true, // Enable horizontal scrolling
        "scrollCollapse": true, // Collapse the table to fit the content if less than set height
        "scrollXInner": "100%", // Allow horizontal scrolling to extend beyond the table width
      });
    });



    function updateFormValues() {
      const currency = localStorage.getItem("currency");
      const exchangeRate = $('#exchange-rate').data('exchange-rate');

      // Update both forms
      ['#add-medicine-form', '#edit-medicine-form'].forEach(formSelector => {
        // Update currency
        $(`${formSelector} input[name="currency"]`).val(currency);
        // Update exchange rate
        $(`${formSelector} input[name="exchange_rate"]`).val(exchangeRate);
      });
    }

    updateFormValues(); // Ensure form values are updated on page load

    // Update form values when the currency is changed
    $("#currency-select").on('change', function() {
      updateFormValues(); // Update form values before reloading to ensure consistency
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
          $('#current-img').attr('src', `../uploads/${medicine.image}`);
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

    $("#edit-medicine-form").on('submit', function(e) {
      e.preventDefault();

      $.ajax({
        url: '../modules/medicines/update_medicine.php',
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function(response) {
          location.reload();
        },
        error: function(xhr, status, error) {
          alert(xhr.responseText);
        },
      });
    });


    function deleteMedicine(id) {
      if (confirm('دڵنیایت کە دەتەوێت ئەم دەرمانە بسڕیتەوە؟')) {
        $.ajax({
          url: '../modules/medicines/delete_medicine.php',
          type: 'POST',
          data: {
            id: id
          },
          success: function(response) {

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