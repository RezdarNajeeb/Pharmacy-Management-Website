$(document).ready(function () {
  const sales = [];
  const salesTableBody = $("#sales-table tbody");
  const totalPriceElement = $("#total-price");

  $("#sales-form").on("submit", function (event) {
    event.preventDefault();

    const medicineId = $("#medicine-id").val();
    const quantity = parseInt($("#quantity").val());

    if (!medicineId || !quantity || quantity < 1) {
      alert("Please select a medicine and enter a valid quantity.");
      return;
    }

    $.ajax({
      url: "../get_medicine_details.php",
      method: "GET",
      data: {
        id: medicineId,
      },
      dataType: "json",
      success: function (data) {
        if (data.status === "error") {
          alert(data.message);
          return;
        }

        const id = data.id;
        const name = data.name;
        const price = parseFloat(data.price);

        const total = price * quantity;

        sales.push({
          id,
          name,
          quantity,
          price,
          total,
        });
        updateSalesTable();
      },
      error: function (xhr, status, error) {
        console.error("Error fetching medicine details:", error);
        alert("Failed to fetch medicine details.");
      },
    });
  });

  function updateSalesTable() {
    salesTableBody.empty();
    let totalPrice = 0;

    sales.forEach((sale, index) => {
      const row = $("<tr></tr>");

      row.append(`<td>${sale.name}</td>`);
      row.append(`<td>${sale.quantity}</td>`);
      row.append(`<td>${sale.price.toFixed(2)}</td>`);
      row.append(`<td>${sale.total.toFixed(2)}</td>`);
      row.append(
        `<td><button type="button" class="remove-sale" data-index="${index}">Remove</button></td>`
      );

      salesTableBody.append(row);
      totalPrice += sale.total;
    });

    totalPriceElement.text(totalPrice.toFixed(2));
  }

  $(document).on("click", ".remove-sale", function () {
    const index = $(this).data("index");
    sales.splice(index, 1);
    updateSalesTable();
  });

  $("#finalize-sale").on("click", function () {
    if (sales.length === 0) {
      alert("No items in the sale.");
      return;
    }

    $.ajax({
      url: "../finalize_sale.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(sales), // Convert the sales array to a JSON string
      dataType: "json",
      success: function (data) {
        alert(data.message);

        if (data.status === "success") {
          sales.length = 0;
          updateSalesTable();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error finalizing sale:", error);
        alert("Failed to finalize sale.");
      },
    });
  });
});
