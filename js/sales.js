$(document).ready(function () {
  let currencySystem = localStorage.getItem("currency");
  const exchangeRate = parseFloat($("#exchange-rate").data("exchange-rate"));
  const sales = [];

  const $totalPriceUSDElement = $("#total-price-usd");
  const $totalPriceIQDElement = $("#total-price-iqd");
  const $discountedTotalPriceUSDElement = $("#discounted-total-price-usd");
  const $discountedTotalPriceIQDElement = $("#discounted-total-price-iqd");
  const $discountField = $("#discount");
  const $discountError = $("#discount-error");

  const $saleInputElement = $("#add-sale-input");
  const $saleQtyElement = $("#quantity");

  // Focus on the sale input field when the page loads
  $saleInputElement.focus();

  // Initialize currency system from local storage or default to "USD"
  if (!currencySystem) {
    currencySystem = "USD";
    localStorage.setItem("currency", currencySystem);
  }
  $("#currency-select").val(currencySystem);

  // Update currency system on change
  $("#currency-select").change(function () {
    currencySystem = $(this).val();
    localStorage.setItem("currency", currencySystem);
    updateDiscountedTotals(); // Update totals based on the new currency
  });

  // Add a new sale item
  $("#add-sale-form").on("submit", function (event) {
    event.preventDefault();

    const $saleInputError = $("#sale-input-error");
    const $saleQtyError = $("#sale-qty-error");

    const saleInput = $saleInputElement.val().trim();
    const quantity = parseInt($saleQtyElement.val());

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
        const currency = medicine.currency;
        const quantityInDB = parseInt(medicine.quantity);
        const expiryDate = medicine.expiry_date;

        if (quantity > quantityInDB) {
          alert(
            `ئەو بڕە دەرمانەی [ ${name} ] کە داواکراوە، زیاترە لەو بڕەی کە لە سیستەم هەیە.`
          );
          return;
        }

        if (expiryDate !== null) {
          const today = new Date();
          const expiry = new Date(expiryDate);
          const warningDate = new Date(today);
          warningDate.setDate(today.getDate() + warningExpiryDays);
          const isExpire = expiry <= warningDate;
          if (isExpire) {
            alert(
              `بەسەرچوونی ئەم دەرمانە [ ${name} ] گەشتووەتە بڕی ئاگادارکردنەوە.`
            );
            return;
          }
        }

        // Check if the medicine is already in sales, update quantity instead of adding a new row
        let existingSale = sales.find((sale) => sale.id === id);
        if (existingSale) {
          existingSale.quantity += quantity;
          if (existingSale.currency === "USD") {
            existingSale.totalUSD = existingSale.quantity * sellingPrice;
            existingSale.totalIQD = existingSale.totalUSD * exchangeRate;
          } else {
            existingSale.totalIQD = existingSale.quantity * sellingPrice;
            existingSale.totalUSD = existingSale.totalIQD / exchangeRate;
          }
        } else {
          if (currency === "USD") {
            var totalUSD = quantity * sellingPrice;
            var totalIQD = totalUSD * exchangeRate;
          } else {
            var totalIQD = quantity * sellingPrice;
            var totalUSD = totalIQD / exchangeRate;
          }

          sales.push({
            id,
            name,
            image,
            quantity,
            costPrice,
            sellingPrice,
            currency,
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

    $saleQtyElement.val(1);
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

          const imageUrl =
            sale.image &&
            sale.image !== "null" &&
            sale.image !== null &&
            sale.image !== ""
              ? `../uploads/${sale.image}`
              : "../assets/images/no-image.avif";

          return `
                <tr>
                    <td><img src="${imageUrl}" alt="Medicine Image"></td>
                    <td>${sale.name}</td>
                    <td>${sale.quantity}</td>
                    <td>
                    ${
                      sale.currency === "USD"
                        ? `${sale.costPrice.toFixed(2)} $<br><br>${(
                            sale.costPrice * exchangeRate
                          ).toFixed(0)} د.ع`
                        : `${(sale.costPrice / exchangeRate).toFixed(
                            2
                          )} $<br><br>${parseFloat(sale.costPrice).toFixed(
                            0
                          )} د.ع`
                    }
                    </td>
                    <td>
                    ${
                      sale.currency === "USD"
                        ? `${sale.sellingPrice.toFixed(2)} $<br><br>${(
                            sale.sellingPrice * exchangeRate
                          ).toFixed(0)} د.ع`
                        : `${(sale.sellingPrice / exchangeRate).toFixed(
                            2
                          )} $<br><br>${parseFloat(sale.sellingPrice).toFixed(
                            0
                          )} د.ع`
                    }
                    </td>
                    <td>
                        ${sale.totalUSD.toFixed(2)} $
                        <br><br>
                        ${sale.totalIQD.toFixed(2)} د.ع
                    </td>
                    <td>
                        <div class="actions">
                          <button type="button" class="increase-quantity light-green-btn" data-index="${index}"><i class="fa-solid fa-plus"></i></button>
                          <button type="button" class="reduce-quantity light-yellow-btn" data-index="${index}"><i class="fa-solid fa-minus"></i></button>
                          <button type="button" class="remove-sale red-btn" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
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

  // Validate discount input
  function validateDiscountInput(discount, totalPrice) {
    if (
      isNaN(discount) ||
      discount === "" ||
      discount === null ||
      discount === undefined
    ) {
      $discountError.text("داشکاندن دەبێت ژمارە بێت.").css("display", "block");
      return false;
    }
    if (discount < 0) {
      $discountError
        .text("داشکاندن نابێت کەمتر بێت لە ٠.")
        .css("display", "block");
      return false;
    }
    if (totalPrice < discount) {
      $discountError
        .text("داشکاندن زیاترە لە کۆی گشتی فرۆشتنەکە.")
        .css("display", "block");
      return false;
    }
    $discountError.css("display", "none");
    return true;
  }

  function updateDiscountedTotals() {
    const totalPriceUSD = parseFloat($totalPriceUSDElement.text());
    const totalPriceIQD = parseFloat($totalPriceIQDElement.text());
    const discount = $discountField.val();

    let discountedTotalPriceUSD = totalPriceUSD;
    let discountedTotalPriceIQD = totalPriceIQD;

    if (
      currencySystem === "USD" &&
      validateDiscountInput(discount, totalPriceUSD)
    ) {
      discountedTotalPriceUSD -= discount;
      discountedTotalPriceIQD = discountedTotalPriceUSD * exchangeRate;
    } else if (
      currencySystem === "IQD" &&
      validateDiscountInput(discount, totalPriceIQD)
    ) {
      discountedTotalPriceIQD -= discount;
      discountedTotalPriceUSD = discountedTotalPriceIQD / exchangeRate;
    }

    $discountedTotalPriceUSDElement.text(discountedTotalPriceUSD.toFixed(2));
    $discountedTotalPriceIQDElement.text(discountedTotalPriceIQD.toFixed(0));
  }

  $discountField.on("input", updateDiscountedTotals);

  const $document = $(document);
  const $finalizeSaleButton = $("#finalize-sale");

  // Event handler to finalize the sale
  $finalizeSaleButton.on("click", function () {
    if (sales.length === 0) {
      alert("هیچ دەرمانێک لە لیستی فرۆشتندا نییە.");
      return;
    }

    const discount = $discountField.val();
    const discountedTotalIQD =
      parseFloat($discountedTotalPriceIQDElement.text()) || 0;

    if (
      (currencySystem === "USD" &&
        !validateDiscountInput(
          discount,
          parseFloat($totalPriceUSDElement.text())
        )) ||
      (currencySystem === "IQD" &&
        !validateDiscountInput(
          discount,
          parseFloat($totalPriceIQDElement.text())
        ))
    ) {
      return;
    }

    const saleData = {
      sales: sales,
      discount: discount,
      discountCurrency: currencySystem,
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

  $document.on("click", ".increase-quantity", function () {
    const index = $(this).data("index");
    sales[index].quantity++;
    sales[index].totalUSD =
      sales[index].currency === "USD"
        ? sales[index].quantity * sales[index].sellingPrice
        : (sales[index].quantity * sales[index].sellingPrice) / exchangeRate;
    sales[index].totalIQD =
      sales[index].currency === "IQD"
        ? sales[index].quantity * sales[index].sellingPrice
        : sales[index].quantity * sales[index].sellingPrice * exchangeRate;
    updateSalesTable();
  });

  $document.on("click", ".reduce-quantity", function () {
    const index = $(this).data("index");
    if (sales[index].quantity > 1) {
      sales[index].quantity--;
      sales[index].totalUSD =
        sales[index].currency === "USD"
          ? sales[index].quantity * sales[index].sellingPrice
          : (sales[index].quantity * sales[index].sellingPrice) / exchangeRate;
      sales[index].totalIQD =
        sales[index].currency === "IQD"
          ? sales[index].quantity * sales[index].sellingPrice
          : sales[index].quantity * sales[index].sellingPrice * exchangeRate;
    } else {
      sales.splice(index, 1);
    }
    updateSalesTable();
  });

  $document.on("click", ".remove-sale", function () {
    const index = $(this).data("index");
    sales.splice(index, 1);
    updateSalesTable();
  });

  // Sales history table with details modal
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

        $modal
          .find("h2")
          .text(`وردەکاریی فرۆشتنی ژمارە ${number}`)
          .css("margin-bottom", "1rem");

        const data = response.data;
        const sale = JSON.parse(data.sale_details);

        const totalIQD = parseFloat(data.totalIQD);
        const totalUSD = totalIQD / exchangeRate;

        const discountCurrency = data.discount_currency;
        if (discountCurrency === "USD") {
          var discountUSD = parseFloat(data.discount);
          var discountIQD = discountUSD * exchangeRate;
        } else {
          var discountIQD = parseFloat(data.discount);
          var discountUSD = discountIQD / exchangeRate;
        }

        const discountedTotalIQD = parseFloat(data.discounted_totalIQD);
        const discountedTotalUSD = discountedTotalIQD / exchangeRate;

        let saleDetailsHtml = `<table>
            <thead>
              <tr>
                <th>وێنە</th>
                <th>ناو</th>
                <th>نرخی فرۆشتن</th>
                <th>بڕ</th>
                <th>کۆی گشتی</th>
              </tr>
            </thead>
            <tbody>`;
        sale.forEach(function (item) {
          const imageUrl =
            item.image &&
            item.image !== "null" &&
            item.image !== null &&
            item.image !== ""
              ? `../uploads/${item.image}`
              : "../assets/images/no-image.avif";

          saleDetailsHtml += `<tr>
              <td><img src="${imageUrl}" alt="Medicine Image"></td>
              <td>${item.name}</td>
              <td>${
                item.currency === "USD"
                  ? `${item.sellingPrice} $ <br><br> ${
                      item.sellingPrice * exchangeRate
                    } IQD`
                  : `${item.sellingPrice} IQD <br><br> ${
                      item.sellingPrice / exchangeRate
                    } $`
              }</td>
              <td>${item.quantity}</td>
              <td>
                ${item.totalUSD.toFixed(2)} $
                 <br/><br/>
                ${item.totalIQD} IQD
              </td>
            </tr>`;
        });

        saleDetailsHtml += `</tbody>
            <tfoot>
              <tr>
                <td colspan="3">کۆی گشتی فرۆشتنەکە</td>
                <td colspan="2">${totalUSD} $ <br><br> ${totalIQD} IQD</td>
              </tr>
              <tr>
                <td colspan="3">داشکاندن</td>
                <td colspan="2">${discountUSD} $ <br><br> ${discountIQD} IQD</td>
              </tr>
              <tr>
                <td colspan="3">کۆی گشتی فرۆشتنەکە دوای داشکاندن</td>
                <td colspan="2">${discountedTotalUSD} $ <br><br> ${discountedTotalIQD} IQD</td>
              </tr>
            </tfoot>
          </table>
          <p>ئەم فرۆشتنە ئەنجام دراوە لەلایەن ${username} لە بەرواری ${saleDate}.</p>`;

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
});
