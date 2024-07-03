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

  $("#sales-form").on("submit", function (event) {
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
        alert("Failed to fetch medicine details.");
      },
    });
  }

  function updateSalesTable() {
    salesTableBody.empty();
    let totalPriceUSD = 0;
    let totalPriceIQD = 0;

    sales.forEach((sale, index) => {
      const row = $("<tr></tr>");

      row.append(`<td>${sale.name}</td>`);
      row.append(`<td>${sale.quantity}</td>`);
      row.append(
        `<td>IQD${sale.costPrice.toFixed(2)}<br>
          $${(sale.costPrice / exchangeRate).toFixed(2)}
        </td>`
      );
      row.append(
        `<td>IQD${sale.sellingPrice.toFixed(2)}<br>
          $${(sale.sellingPrice / exchangeRate).toFixed(2)}
        </td>`
      );
      row.append(
        `<td>$${sale.totalUSD.toFixed(2)}<br>
          IQD${sale.totalIQD.toFixed(2)}
        </td>`
      );
      row.append(
        `<td><button type="button" class="remove-sale" data-index="${index}">Remove</button> 
         <button type="button" class="reduce-quantity" data-index="${index}">Reduce Quantity</button></td>`
      );

      salesTableBody.append(row);
      totalPriceUSD += sale.totalUSD;
      totalPriceIQD += sale.totalIQD;
    });

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

  // Event handler for discount input field
  discountField.on("input", function () {
    updateDiscountedTotals();
  });

  // Focus on barcode input field on page load
  $("#medicine-barcode").focus();
});
