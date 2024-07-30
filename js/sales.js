$(document).ready(function () {
  const currency = localStorage.getItem("currency");
  const exchangeRate = parseFloat($("#exchange-rate").data("exchange-rate"));
  const sales = [];

  const $totalPriceUSDElement = $("#total-price-usd");
  const $totalPriceIQDElement = $("#total-price-iqd");
  const $discountedTotalPriceUSDElement = $("#discounted-total-price-usd");
  const $discountedTotalPriceIQDElement = $("#discounted-total-price-iqd");
  const $discountField = $("#discount");

  const $saleInputElement = $("#add-sale-input");

  $("#add-sale-form").on("submit", function (event) {
    event.preventDefault();

    const $saleInputError = $("#sale-input-error");
    const $saleQtyError = $("#sale-qty-error");

    const saleInput = $saleInputElement.val().trim();
    const quantity = parseInt($("#quantity").val());

    let barcode = null;
    let medicineName = null;

    if (isNaN(saleInput)) {
      medicineName = saleInput;
      if (!medicineName) {
        $saleInputError.text("ناوی دەرمان یان بارکۆد پێویستە پڕبکرێتەوە.");
        $saleInputError.css("display", "block");
        return;
      }
    } else {
      barcode = saleInput;
      if (!barcode || barcode.length !== 13) {
        $saleInputError.text(
          !barcode
            ? "ناوی دەرمان یان بارکۆد پێویستە پڕبکرێتەوە."
            : "بارکۆد پێویستە لە ١٣ پیت بێت."
        );
        $saleInputError.css("display", "block");
        return;
      }
    }

    if (!quantity) {
      $saleQtyError.text("بڕ پێویستە پڕبکرێتەوە.");
      $saleQtyError.css("display", "block");
      return;
    } else if (quantity < 1) {
      $saleQtyError.text("بڕ پێویستە زیاتر بێت لە ١.");
      $saleQtyError.css("display", "block");
      return;
    }

    $saleInputError.css("display", "none");
    $saleQtyError.css("display", "none");

    $.ajax({
      url: "../modules/sales/get_medicine_details.php",
      method: "GET",
      data: { barcode: barcode, medicine_name: medicineName },
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

    $saleInputElement.val("").focus();
  });

  function updateSalesTable() {
    const $salesTableBody = $("#sales-table tbody").empty();
    let totalPriceUSD = 0;
    let totalPriceIQD = 0;

    if (sales.length === 0) {
      $salesTableBody.append(
        "<tr><td colspan='7'>هیچ دەرمانێک لە لیستی فرۆشتندا نییە.</td></tr>"
      );
    } else {
      const rows = sales
        .map((sale, index) => {
          totalPriceUSD += sale.totalUSD;
          totalPriceIQD += sale.totalIQD;

          return `
                <tr>
                    <td><img src="../uploads/${sale.image}" alt="${
            sale.name
          }"></td>
                    <td>${sale.name}</td>
                    <td>${sale.quantity}</td>
                    <td>
                        ${(sale.costPrice / exchangeRate).toFixed(2)} $
                        <br><br>
                        ${sale.costPrice.toFixed(2)} د.ع
                    </td>
                    <td>
                        ${(sale.sellingPrice / exchangeRate).toFixed(2)} $
                        <br><br>
                        ${sale.sellingPrice.toFixed(2)} د.ع
                    </td>
                    <td>
                        ${sale.totalUSD.toFixed(2)} $
                        <br><br>
                        ${sale.totalIQD.toFixed(2)} د.ع
                    </td>
                    <td>
                        <div class="actions">
                          <button type="button" class="increase-quantity light-green-btn" data-index="${index}">زیادکردن</button>
                          <button type="button" class="reduce-quantity light-blue-btn" data-index="${index}">کەمکردنەوە</button>
                          <button type="button" class="remove-sale red-btn" data-index="${index}">سڕینەوە</button>
                        </div>
                    </td>
                </tr>
            `;
        })
        .join("\n");

      $salesTableBody.append(rows);
    }

    $totalPriceUSDElement.text(totalPriceUSD.toFixed(2));
    $totalPriceIQDElement.text(totalPriceIQD.toFixed(2));
    updateDiscountedTotals();
  }

  function updateDiscountedTotals() {
    const totalPriceUSD = parseFloat($totalPriceUSDElement.text());
    const totalPriceIQD = parseFloat($totalPriceIQDElement.text());
    const discount = parseFloat($discountField.val()) || 0;

    let discountedTotalPriceUSD = totalPriceUSD;
    let discountedTotalPriceIQD = totalPriceIQD;

    if (currency === "USD") {
      discountedTotalPriceUSD -= discount;
      discountedTotalPriceIQD = discountedTotalPriceUSD * exchangeRate;
    } else if (currency === "IQD") {
      discountedTotalPriceIQD -= discount;
      discountedTotalPriceUSD = discountedTotalPriceIQD / exchangeRate;
    }

    $discountedTotalPriceUSDElement.text(discountedTotalPriceUSD.toFixed(2));
    $discountedTotalPriceIQDElement.text(discountedTotalPriceIQD.toFixed(2));
  }

  // Cache jQuery selectors
  const $document = $(document);
  const $finalizeSaleButton = $("#finalize-sale");

  // Event handler to remove a sale item
  $document.on("click", ".remove-sale", function () {
    const index = $(this).data("index");
    sales.splice(index, 1); // splice() removes the element at the specified index
    updateSalesTable();

    $saleInputElement.focus();
  });

  // Event handler to reduce quantity of a sale item
  $document.on("click", ".reduce-quantity", function () {
    const index = $(this).data("index");
    const saleItem = sales[index];

    if (saleItem.quantity > 1) {
      saleItem.quantity--;
      saleItem.totalIQD = saleItem.quantity * saleItem.sellingPrice;
      saleItem.totalUSD = saleItem.totalIQD / exchangeRate;
      updateSalesTable();
    }

    $saleInputElement.focus();
  });

  // Event handler to increase quantity of a sale item
  $document.on("click", ".increase-quantity", function () {
    const index = $(this).data("index");
    const saleItem = sales[index];

    saleItem.quantity++;
    saleItem.totalIQD = saleItem.quantity * saleItem.sellingPrice;
    saleItem.totalUSD = saleItem.totalIQD / exchangeRate;

    updateSalesTable();
    $saleInputElement.focus();
  });

  // Event handler to finalize the sale
  $finalizeSaleButton.on("click", function () {
    if (sales.length === 0) {
      alert("هیچ دەرمانێک لە لیستی فرۆشتندا نییە.");
      return;
    }

    const discount = parseFloat($discountField.val()) || 0;
    const discountedTotalUSD =
      parseFloat($discountedTotalPriceUSDElement.text()) || 0;
    const discountedTotalIQD =
      parseFloat($discountedTotalPriceIQDElement.text()) || 0;

    const $discountError = $("#discount-error");

    if (isNaN(discount) || discount === null) {
      $discountError.text("داشکاندن پێویستە پڕبکرێتەوە و تەنها ژمارە بێت.");
      $discountError.css("display", "block");
      return;
    } else if (discount < 0) {
      $discountError.text("داشکاندن پێویستە بەلایەنی کەمەەوە ٠ بێت.");
      $discountError.css("display", "block");
      return;
    }

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
  $discountField.on("input", function () {
    updateDiscountedTotals();
  });

  $("#sales-history-table").on("click", ".view-sale-details", function () {
    const $this = $(this);
    const saleId = $this.data("id");
    const number = $this.data("number");
    const username = $this.data("username");
    const saleDate = $this.data("sale-date");

    $.ajax({
      url: "../modules/sales/get_sale_details.php",
      method: "GET",
      data: { sale_id: saleId },
      dataType: "json",
      success: function (response) {
        if (response.status === "error") {
          alert(response.message);
          return;
        }

        const $modal = $("#sale-details-modal");
        const $saleDetails = $("#sale-details");

        $modal.find("h2").text(`وردەکاریی فرۆشتنی ژمارە ${number}`).css("margin-bottom", "1rem");

        const data = response.data;
        const sale = JSON.parse(data.sale_details);
        const totalIQD = parseFloat(data.total);
        const discount = parseFloat(data.discount);
        const discountedTotalIQD = parseFloat(data.discounted_total);

        let saleDetailsHtml = `
          <table>
            <thead>
              <tr>
                <th>وێنە</th>
                <th>ناو</th>
                <th>نرخی فرۆشتن</th>
                <th>بڕ</th>
                <th>کۆی گشتی</th>
              </tr>
            </thead>
            <tbody>
        `;

        sale.forEach(function (item) {
          saleDetailsHtml += `
            <tr>
              <td><img src="../uploads/${item.image}" alt="${item.name}"></td>
              <td>${item.name}</td>
              <td>${item.sellingPrice}</td>
              <td>${item.quantity}</td>
              <td>
                ${item.totalIQD} د.ع <br/><br/>
                ${item.totalUSD.toFixed(2)} $
              </td>
            </tr>
          `;
        });

        saleDetailsHtml += `
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3">کۆی گشتی فرۆشتنەکە</td>
                <td colspan="2">${totalIQD}</td>
              </tr>
              <tr>
                <td colspan="3">داشکاندن</td>
                <td colspan="2">${discount}</td>
              </tr>
              <tr>
                <td colspan="3">کۆی گشتی فرۆشتنەکە دوای داشکاندن</td>
                <td colspan="2">${discountedTotalIQD}</td>
              </tr>
            </tfoot>
          </table>
          <p>ئەم فرۆشتنە ئەنجام دراوە لەلایەن ${username} لە بەرواری ${saleDate}.</p>
        `;

        $saleDetails.html(saleDetailsHtml).css("direction", "rtl");

        // Show the modal
        $modal.css("visibility", "visible");
      },
      error: function (error) {
        console.error("Error fetching sale details:", error);
      },
    });
  });

  $(".close").on("click", function () {
    $("#sale-details-modal").css("visibility", "hidden");
  });

  // Close the modal by clicking outside of it
  $(window).click(function (event) {
    if (event.target == $("#sale-details-modal")[0]) {
      $("#sale-details-modal").css("visibility", "hidden");
    }
  });

  // Focus on the sale input field when the page loads
  $saleInputElement.focus();
});
