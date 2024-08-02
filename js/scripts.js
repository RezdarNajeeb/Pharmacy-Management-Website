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

  // Set the currency select value from localStorage if it exists
  var savedCurrency = localStorage.getItem("currency");
  if (savedCurrency) {
    $("#currency-select").val(savedCurrency);
  }

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

  // Update form values with the currency and exchange rate
  function updateFormValues() {
    const currency = localStorage.getItem("currency");
    const exchangeRate = $("#exchange-rate").data("exchange-rate");

    // Update both forms
    ["#add-medicine-form", "#edit-medicine-form"].forEach((formSelector) => {
      // Update currency
      $(`${formSelector} input[name="currency"]`).val(currency);
      // Update exchange rate
      $(`${formSelector} input[name="exchange_rate"]`).val(exchangeRate);
    });
  }

  // Update localStorage and reload the page when the currency is changed
  $("#currency-select").change(function () {
    var currency = $(this).val();
    localStorage.setItem("currency", currency);
    updateFormValues();
  });

  // Unified validation function
  function validateForm(form, formId) {
    let isValid = true;

    const errorMessages = [
      "ناوی دەرمان پێویستە پڕبکرێتەوە.",
      "جۆر پێویستە پڕبکرێتەوە.",
      "نرخی کڕین پێویستە پڕبکرێتەوە.",
      "نرخی فرۆشتن پێویستە پڕبکرێتەوە.",
      "بڕ پێویستە پڕبکرێتەوە.",
      "بەسەرچوونی پێویستە پڕبکرێتەوە.",
      "بارکۆد پێویستە پڕبکرێتەوە.",
    ];

    const inputRegex = /^[a-z][a-z0-9-]*(?:[ -]?[a-z0-9-]+)*$/i;
    const fieldSelectors = {
      0: "ناوی دەرمان",
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
        if (value.length !== 13) {
          addErrorMessage(this, "بارکۆد پێویستە ١٣ ژمارە بێت.");
        } else if (isNaN(value)) {
          addErrorMessage(this, "بارکۆد پێویستە تەنها ژمارە بێت.");
        }
        return;
      }

      if ((index === 2 || index === 3) && value) {
        if (isNaN(value)) {
          addErrorMessage(
            this,
            "نرخی کڕین و نرخی فرۆشتن پێویستە تەنها ژمارە بێت."
          );
        } else if (value <= 0 || value > 1000000) {
          const rangeMessage =
            value <= 0
              ? "نرخی کڕین و نرخی فرۆشتن پێویستە زیاتر بێت لە ١."
              : "نرخی کڕین و نرخی فرۆشتن پێویستە کەمتر بێت لە ١٠٠٠٠٠٠.";
          addErrorMessage(this, rangeMessage);
        }

        const costPriceSelector =
          formId === "add-form-medicine" ? "#cost_price" : "#edit-cost_price";
        const sellingPriceSelector =
          formId === "add-form-medicine"
            ? "#selling_price"
            : "#edit-selling_price";

        const costingPrice = parseFloat($(costPriceSelector).val().trim());
        const sellingPrice = parseFloat($(sellingPriceSelector).val().trim());

        if (costingPrice && sellingPrice && sellingPrice <= costingPrice) {
          if (index === 3) {
            addErrorMessage(
              this,
              "نرخی فرۆشتن پێویستە زیاتر بێت لە نرخی کڕین."
            );
          } else {
            addErrorMessage(
              this,
              "نرخی کڕین پێویستە کەمتر بێت لە نرخی فرۆشتن."
            );
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
          location.reload(); // Reload the page on success
        },
        error: function (xhr, status, error) {
          alert(xhr.responseText); // Show error message on failure
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
