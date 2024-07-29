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
        $(this).css("background-color", "var(--background-color)");
      }
    });
  }

  highlightCurrentPageLink();

  // Set the currency select value from localStorage if it exists
  var savedCurrency = localStorage.getItem("currency");
  if (savedCurrency) {
    $("#currency-select").val(savedCurrency);
  }

  // Update localStorage and reload the page when the currency is changed
  $("#currency-select").change(function () {
    var currency = $(this).val();
    localStorage.setItem("currency", currency);
    location.reload();
  });

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
      $("#image-name").text(fileName + " هەڵبژێردراوە");
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

  // Unified validation function
  function validateForm(form) {
    let isValid = true;

    // Error messages corresponding to each field
    const errorMessages = [
      "ناوی دەرمان پێویستە پڕبکرێتەوە.",
      "جۆر پێویستە پڕبکرێتەوە.",
      "نرخی کڕین پێویستە پڕبکرێتەوە.",
      "نرخی فرۆشتن پێویستە پڕبکرێتەوە.",
      "بڕ پێویستە پڕبکرێتەوە.",
      "بەسەرچوونی پێویستە پڕبکرێتەوە.",
      "بارکۆد پێویستە پڕبکرێتەوە.",
    ];

    // Clear previous error messages
    form.find(".error-field").remove();

    // Common validation rules for edit and add medicine forms
    const fields = form.find(".field");

    fields.each(function (index) {
      const value = $(this).val().trim();
      if (!value && errorMessages[index]) {
        const errorMessage = `<span class="error-field">${errorMessages[index]}</span>`;
        $(errorMessage).insertAfter(this).css("display", "block");
        isValid = false;
      }

      // Barcode specific validation
      if (index === errorMessages.length - 1 && value) {
        if (value.length !== 13) {
          const errorMessage = `<span class="error-field">بارکۆد پێویستە لە ١٣ پیت بێت.</span>`;
          $(errorMessage).insertAfter(this).css("display", "block");
          isValid = false;
        } else if (isNaN(value)) {
          const errorMessage = `<span class="error-field">بارکۆد پێویستە تەنها ژمارە بێت.</span>`;
          $(errorMessage).insertAfter(this).css("display", "block");
          isValid = false;
        }
      }
    });

    return isValid;
  }

  // Form submit event handler
  $("#edit-medicine-form, #add-medicine-form").on("submit", function (event) {
    const form = $(this);
    const formId = form.attr("id"); // Get the form ID correctly

    if (!validateForm(form)) {
      event.preventDefault();
    } else {
      if (formId === "edit-medicine-form") {
        event.preventDefault(); // Prevent the default form submission

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
