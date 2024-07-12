$(document).ready(function () {
  const currencySelect = $("#currency-select");
  const exchangeRate = parseFloat($("#exchange-rate").data("exchange-rate"));
  const sales = [];
  const salesTableBody = $("#sales-table tbody");
  const totalPriceUSDElement = $("#total-price-usd");
  const totalPriceIQDElement = $("#total-price-iqd");
  const discountedTotalPriceUSDElement = $("#discounted-total-price-usd");
  const discountedTotalPriceIQDElement = $("#discounted-total-price-iqd");
  const discountField = $("#discount");

  $("#add-sale-form").on("submit", function (event) {
    event.preventDefault();

    const barcode = $("#medicine-barcode").val().trim();
    const quantity = parseInt($("#quantity").val());

    if (!barcode || !quantity || quantity < 1) {
      alert("Please enter a valid barcode and quantity.");
      return;
    }

    addSaleItem(barcode, quantity);
  });

  function addSaleItem(barcode, quantity) {
    $.ajax({
      url: "../modules/sales/get_medicine_details.php",
      method: "GET",
      data: { barcode: barcode },
      dataType: "json",
      success: function (data) {
        if (data.status === "error") {
          alert(data.message);
          return;
        }

        const medicine = data.medicine;
        const id = medicine.id;
        const name = medicine.name;
        const image = medicine.image;
        const costPrice = parseFloat(medicine.cost_price);
        const sellingPrice = parseFloat(medicine.selling_price);

        // Check if the medicine is already in sales, update quantity instead of adding a new row
        let existingSale = sales.find((sale) => sale.id === id);
        if (existingSale) {
          existingSale.quantity += quantity;
          existingSale.totalIQD = existingSale.quantity * sellingPrice;
          existingSale.totalUSD = existingSale.totalIQD / exchangeRate;
        } else {
          const totalIQD = sellingPrice * quantity;
          const totalUSD = totalIQD / exchangeRate;

          sales.push({
            id,
            name,
            image,
            quantity,
            costPrice,
            sellingPrice,
            totalUSD,
            totalIQD,
          });
        }

        updateSalesTable();
      },
      error: function (xhr, status, error) {
        console.error("Error fetching medicine details:", error);
        window.location.reload();
      },
    });
  }

  function updateSalesTable() {
    salesTableBody.empty();
    let totalPriceUSD = 0;
    let totalPriceIQD = 0;

    if (sales.length === 0) {
      salesTableBody.append(
        "<tr><td colspan='7'>هیچ دەرمانێک لە لیستی فرۆشتندا نییە.</td></tr>"
      );
    } else {
      sales.forEach((sale, index) => {
        const row = $("<tr></tr>");

        row.append(
          `<td><img src="../uploads/${sale.image}" alt="${sale.name}"></td>`
        );
        row.append(`<td>${sale.name}</td>`);
        row.append(`<td>${sale.quantity}</td>`);
        row.append(
          `<td> 
          ${(sale.costPrice / exchangeRate).toFixed(2)} $
          <br><br>
          ${sale.costPrice.toFixed(2)} د.ع
        </td>`
        );
        row.append(
          `<td> 
          ${(sale.sellingPrice / exchangeRate).toFixed(2)} $
          <br><br>
          ${sale.sellingPrice.toFixed(2)} د.ع
        </td>`
        );
        row.append(
          `<td>
          ${sale.totalUSD.toFixed(2)} $
          <br><br>
          ${sale.totalIQD.toFixed(2)} د.ع
        </td>`
        );
        row.append(
          `<td>
          <div class="actions">
            <button type="button" class="remove-sale red-btn" data-index="${index}">سڕینەوە</button> 
            <button type="button" class="reduce-quantity light-blue-btn" data-index="${index}">کەمکردنەوە</button>
          </div>
        </td>`
        );

        salesTableBody.append(row);
        totalPriceUSD += sale.totalUSD;
        totalPriceIQD += sale.totalIQD;
      });
    }

    totalPriceUSDElement.text(totalPriceUSD.toFixed(2));
    totalPriceIQDElement.text(totalPriceIQD.toFixed(2));
    updateDiscountedTotals();
  }

  function updateDiscountedTotals() {
    let totalPriceUSD = parseFloat(totalPriceUSDElement.text());
    let totalPriceIQD = parseFloat(totalPriceIQDElement.text());
    const discount = parseFloat(discountField.val()) || 0;

    if (currencySelect.val() === "USD") {
      totalPriceUSD -= discount;
      totalPriceIQD = totalPriceUSD * exchangeRate;
    } else if (currencySelect.val() === "IQD") {
      totalPriceIQD -= discount;
      totalPriceUSD = totalPriceIQD / exchangeRate;
    }

    discountedTotalPriceUSDElement.text(totalPriceUSD.toFixed(2));
    discountedTotalPriceIQDElement.text(totalPriceIQD.toFixed(2));
  }

  // Event handler to remove a sale item
  $(document).on("click", ".remove-sale", function () {
    const index = $(this).data("index");
    sales.splice(index, 1);
    updateSalesTable();
  });

  // Event handler to reduce quantity of a sale item
  $(document).on("click", ".reduce-quantity", function () {
    const index = $(this).data("index");
    if (sales[index].quantity > 1) {
      sales[index].quantity--;
      sales[index].totalIQD = sales[index].quantity * sales[index].sellingPrice;
      sales[index].totalUSD = sales[index].totalIQD / exchangeRate;
      updateSalesTable();
    }
  });

  // Event handler to finalize the sale
  $("#finalize-sale").on("click", function () {
    if (sales.length === 0) {
      alert("No items in the sale.");
      return;
    }

    const discount = parseFloat(discountField.val()) || 0;
    const discountedTotalUSD = parseFloat(
      discountedTotalPriceUSDElement.text()
    );
    const discountedTotalIQD = parseFloat(
      discountedTotalPriceIQDElement.text()
    );

    const saleData = {
      sales: sales,
      discount: discount,
      discountedTotalUSD: discountedTotalUSD,
      discountedTotalIQD: discountedTotalIQD,
    };

    $.ajax({
      url: "../modules/sales/finalize_sale.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(saleData), // Convert the sales array to a JSON string
      success: function (response) {
        location.reload();
        sales.length = 0;
        updateSalesTable();
      },
      error: function (xhr, status, error) {
        console.error("Error finalizing sale:", error);
        alert("Failed to finalize sale.");
      },
    });
  });

  // Event handler for discount input field
  discountField.on("input", function () {
    updateDiscountedTotals();
  });

  // Focus on barcode input field on page load
  $("#medicine-barcode").focus();
});
