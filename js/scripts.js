$(function () {
  $(window).scroll(function () {
    var header = $("#header");
    var scrollTop = $(this).scrollTop();
    header.toggleClass("sticky", scrollTop > 0);
  });

  function highlightCurrentPageLink() {
    // Get the current page URL
    var currentUrl = window.location.pathname;
    // Get the filename from the URL
    var filename = currentUrl.substring(currentUrl.lastIndexOf("/") + 1);

    $(".navbar ul li a").each(function () {
      var link = $(this).attr("href");
      if (link === filename) {
        $(this).addClass("active");
      }
    });
  }

  highlightCurrentPageLink();

  function setMinExpiryDate() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, "0");
    var mm = String(today.getMonth() + 1).padStart(2, "0"); // January is 0!
    var yyyy = today.getFullYear();

    var minDate = yyyy + "-" + mm + "-" + dd;
    $("#edit-expiry_date").attr("min", minDate);
    $("#expiry_date").attr("min", minDate);
  }

  setMinExpiryDate();

  function setupModalHandlers(
    modalSelector,
    triggerBtnSelector,
    closeBtnSelector
  ) {
    var modal = $(modalSelector);
    var triggerBtn = $(triggerBtnSelector);
    var closeBtn = $(closeBtnSelector);

    // Show the modal
    triggerBtn.click(function () {
      modal.css("visibility", "visible");
    });

    // Close the modal with the close button
    closeBtn.click(function () {
      modal.css("visibility", "hidden");
    });

    // Close the modal by clicking outside of it
    $(window).click(function (event) {
      if (event.target == modal[0]) {
        modal.css("visibility", "hidden");
      }
    });
  }

  setupModalHandlers("#account-modal", "#update-user", ".close");
  setupModalHandlers("#edit-medicine-modal", null, null); // Assuming there's a trigger button and close button specific to this modal

  function handleUserBox() {
    var userIcon = $("#user-icon");
    var userBox = $("#user-box");

    // When click on the userIcon, toggle the user-box
    userIcon.click(function (event) {
      event.stopPropagation(); // Prevents the window click event from triggering
      userBox.toggleClass("show");
    });

    // When click on the userBox, prevent the window click event from triggering
    userBox.click(function (event) {
      event.stopPropagation();
    });

    // When click on the update-user button, close the user-box
    $("#update-user").click(function (event) {
      userBox.removeClass("show");
    });

    // When click anywhere in the document outside the userIcon and userBox, close the user-box
    $(window).click(function (event) {
      userBox.removeClass("show");
    });

    // When scroll, close the user-box
    $(window).scroll(function () {
      userBox.removeClass("show");
    });
  }

  handleUserBox();

  // Handle the show password checkbox for the account form
  const showPasswordCheckbox = $("#show-password");
  const passwordFields = $("#new-password, #current-password");

  showPasswordCheckbox.on("change", function () {
    const type = this.checked ? "text" : "password";
    passwordFields.prop("type", type);
  });

  // Handle the account form submission
  $("#account-form").on("submit", function (event) {
    // Get input values
    const newUsername = $("#new-username").val().trim();
    const currentPassword = $("#current-password").val().trim();
    const newPassword = $("#new-password").val().trim();

    // Get error message elements
    let $newUsernameError = $("#new-username-error");
    let $currentPasswordError = $("#current-password-error");
    let $newPasswordError = $("#new-password-error");

    // Clear previous error messages
    $($newUsernameError, $currentPasswordError, $newPasswordError).text("");

    const usernameRegex = /^[a-zA-Z].*$/;
    const passwordRegex = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;

    // Function to set error message and prevent form submission
    function setError($element, message) {
      $element.text(message).css("display", "block");
      event.preventDefault();
    }

    // Validate new username
    if (!newUsername) {
      setError($newUsernameError, "ناوی نوێ پێویستە پڕبکرێتەوە.");
    } else if (!usernameRegex.test(newUsername)) {
      setError($newUsernameError, "ناوی نوێ پێویستە بە پیت دەست پێبکات.");
    } else if (newUsername.length < 3) {
      setError(
        $newUsernameError,
        "ناوی نوێ پێویستە بەلایەنی کەمەوە ٣ پیت بێت."
      );
    } else if (newUsername.length > 20) {
      setError(
        $newUsernameError,
        "ناوی نوێ پێویستە بەلایەنی زۆرەوە ٢٠ پیت بێت."
      );
    }

    // Validate current password
    if (!currentPassword) {
      setError($currentPasswordError, "وشەی نهێنی ئێستا پێویستە پڕبکرێتەوە.");
    } else if (currentPassword.length < 8) {
      setError(
        $currentPasswordError,
        "وشەی نهێنی ئێستا پێویستە بەلایەنی کەمەوە ٨ پیت بێت."
      );
    } else if (!passwordRegex.test(currentPassword)) {
      setError(
        $currentPasswordError,
        "وشەی نهێنی ئێستا پێویستە بەلایەنی کەمەوە ٨ پیت بێت و بەلایەنی کەمەوە ژمارەیەک و پیتێک و هێمایەکی تێدابێت."
      );
    }

    // Validate new password
    if (!newPassword) {
      setError($newPasswordError, "وشەی نهێنی نوێ پێویستە پڕبکرێتەوە.");
    } else if (newPassword.length < 8) {
      setError(
        $newPasswordError,
        "وشەی نهێنی نوێ پێویستە بەلایەنی کەمەوە ٨ پیت بێت."
      );
    } else if (!passwordRegex.test(newPassword)) {
      setError(
        $newPasswordError,
        "وشەی نهێنی نوێ پێویستە بەلایەنی کەمەوە ٨ پیت بێت و بەلایەنی کەمەوە ژمارەیەک و پیتێک و هێمایەکی تێدابێت."
      );
    } else if (newPassword === currentPassword) {
      setError(
        $newPasswordError,
        "وشەی نهێنی نوێ نابێت وەک وشەی نهێنی ئێستا بێت."
      );
    }
  });

  function updateFileNameDisplay() {
    // for add-medicine
    $("#image-input").on("change", function () {
      // .prop() is used to get the value of properties means attributes of the element
      var fileName = $(this).prop("files")[0].name;
      $("#image-name").html(`<span>${fileName}</span> هەڵبژێردراوە`);
    });
  }

  updateFileNameDisplay();

  function setupImagePreview(inputSelector, imageSelector) {
    $(inputSelector).change(function () {
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $(imageSelector).attr("src", e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  setupImagePreview("#profileImageInput", "#profileImage");
  setupImagePreview("#edit-image", "#current-img");

  // Set the currency select value from localStorage if it exists
  const savedCurrency = localStorage.getItem("currency");
  if (savedCurrency) {
    $("#currency-select").val(savedCurrency);
  }

  // Update form values with the currency and exchange rate
  function updateFormValues() {
    const currency = localStorage.getItem("currency");
    const exchangeRate = $("#exchange-rate").data("exchange-rate");

    if (!exchangeRate) {
      alert("نرخی ئەمڕۆی دۆلار نەزانراوە.");
      return;
    }

    // Update both forms
    ["#add-medicine-form", "#edit-medicine-form"].forEach((formSelector) => {
      // Update currency
      $(`${formSelector} input[name="currency"]`).val(currency);
      // Update exchange rate
      $(`${formSelector} input[name="exchange_rate"]`).val(exchangeRate);
    });
  }

  // Call updateFormValues on page load to set initial values
  updateFormValues();

  // Update localStorage when the currency is changed
  $("#currency-select").change(function () {
    const currency = $(this).val();
    localStorage.setItem("currency", currency);
    updateFormValues();
  });

  // Unified validation function
  function validateForm(form, formId) {
    let isValid = true;

    const errorMessages = [
      "ناوی بەرهەم پێویستە پڕبکرێتەوە.",
      "جۆر پێویستە پڕبکرێتەوە.",
      "نرخی کڕین پێویستە پڕبکرێتەوە.",
      "نرخی فرۆشتن پێویستە پڕبکرێتەوە.",
      "بڕ پێویستە پڕبکرێتەوە.",
    ];

    const inputRegex = /^[a-z][a-z0-9-]*(?:[ -]?[a-z0-9-]+)*$/i;
    const fieldSelectors = {
      0: "ناوی بەرهەم",
      1: "جۆر",
    };

    form.find(".error-field").remove();
    const fields = form.find(".field");

    const addErrorMessage = (element, message) => {
      const errorMessage = `<span class="error-field">${message}</span>`;
      $(errorMessage)
        .insertAfter($(element).parent())
        .css({
          display: "block",
          margin:
            formId === "edit-medicine-form" ? "-1rem 0 1rem 0" : "0 0 1rem 0",
        });

      isValid = false;
    };

    const costPriceSelector =
      formId === "add-medicine-form" ? "#cost_price" : "#edit-cost_price";
    const sellingPriceSelector =
      formId === "add-medicine-form" ? "#selling_price" : "#edit-selling_price";

    fields.each(function (index) {
      const value = $(this).val().trim();

      if (!value && errorMessages[index]) {
        addErrorMessage(this, errorMessages[index]);
        return;
      }

      if (fieldSelectors[index] && value && !inputRegex.test(value)) {
        addErrorMessage(
          this,
          `${fieldSelectors[index]} دەبێت بە پیت دەست پێبکات و تەنها ژمارە و پیت و بۆشایی و - ڕێگەپێدراوە.`
        );
        return;
      }

      if (index === 6 && value) {
        if (isNaN(value)) {
          addErrorMessage(this, "بارکۆد پێویستە تەنها ژمارە بێت.");
        }
        return;
      }

      if ((index === 2 || index === 3) && value) {
        const costingPrice = parseFloat($(costPriceSelector).val().trim());
        const sellingPrice = parseFloat($(sellingPriceSelector).val().trim());

        if (isNaN(costingPrice) || isNaN(sellingPrice)) {
          addErrorMessage(
            this,
            "نرخی کڕین و نرخی فرۆشتن پێویستە تەنها ژمارە بێت."
          );
        } else {
          if (costingPrice <= 0) {
            addErrorMessage(this, "نرخی کڕین پێویستە زیاتر بێت لە ٠.");
          }
          if (sellingPrice <= 0) {
            addErrorMessage(this, "نرخی فرۆشتن پێویستە زیاتر بێت لە ٠.");
          }
          if (sellingPrice <= costingPrice) {
            const errorMessage =
              index === 3
                ? "نرخی فرۆشتن پێویستە زیاتر بێت لە نرخی کڕین."
                : "نرخی کڕین پێویستە کەمتر بێت لە نرخی فرۆشتن.";
            addErrorMessage(this, errorMessage);
          }
        }
      }
    });

    return isValid;
  }

  // Form submit event handler
  $("#edit-medicine-form, #add-medicine-form").on("submit", function (event) {
    const form = $(this);
    const formId = form.attr("id");

    if (!validateForm(form, formId)) {
      event.preventDefault();
    } else if (formId === "edit-medicine-form") {
      event.preventDefault();

      // Handle AJAX request for the edit-medicine-form
      $.ajax({
        url: "../modules/medicines/update_medicine.php",
        type: "POST",
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function (response) {
          location.reload();
        },
        error: function (xhr, status, error) {
          console.error(error);
        },
      });
    }
  });

  // Update exchange rate form validation
  $("#update-exc-rate-form").on("submit", function (event) {
    const $excRateInput = $("#exchange-rate-input");
    const excRate = $excRateInput.val().trim();

    if (!excRate) {
      alert("نرخی ئەمڕۆ پێویستە پڕبکرێتەوە.");
      event.preventDefault();
    } else if (isNaN(excRate)) {
      alert("نرخی ئەمڕۆ پێویستە تەنها ژمارە بێت.");
      event.preventDefault();
    } else if (excRate <= 0) {
      alert("نرخی ئەمڕۆ پێویستە زیاتر بێت لە ٠.");
      event.preventDefault();
    }
  });
});
