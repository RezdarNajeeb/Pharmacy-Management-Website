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

  function updateDiscountedTotals() {
    const totalPriceUSD = parseFloat($totalPriceUSDElement.text());
    const totalPriceIQD = parseFloat($totalPriceIQDElement.text());
    const discount = parseFloat($discountField.val()) || 0;

    let discountedTotalPriceUSD = totalPriceUSD;
    let discountedTotalPriceIQD = totalPriceIQD;

    if (currencySystem === "USD") {
      if (discountedTotalPriceUSD < discount) {
        $discountError
          .text("داشکاندن زیاترە لە کۆی گشتی فرۆشتنەکە.")
          .css("display", "block");
        return;
      } else {
        $discountError.css("display", "none");
        discountedTotalPriceUSD -= discount;
        discountedTotalPriceIQD = discountedTotalPriceUSD * exchangeRate;
      }
    } else if (currencySystem === "IQD") {
      if (discountedTotalPriceIQD < discount) {
        $discountError
          .text("داشکاندن زیاترە لە کۆی گشتی فرۆشتنەکە.")
          .css("display", "block");
        return;
      } else {
        $discountError.css("display", "none");
        discountedTotalPriceIQD -= discount;
        discountedTotalPriceUSD = discountedTotalPriceIQD / exchangeRate;
      }
    }

    $discountedTotalPriceUSDElement.text(discountedTotalPriceUSD.toFixed(2));
    $discountedTotalPriceIQDElement.text(discountedTotalPriceIQD.toFixed(0));
  }

  $discountField.on("input", updateDiscountedTotals);

  $(document).on("click", ".increase-quantity", function () {
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

  $(document).on("click", ".reduce-quantity", function () {
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

  $(document).on("click", ".remove-sale", function () {
    const index = $(this).data("index");
    sales.splice(index, 1);
    updateSalesTable();
  });
});
