$(document).ready(function () {
  // Form validation
  var form = $("form");
  var usernameError = $("#username-error");
  var passwordError = $("#password-error");
  var confirmPasswordError = $("#confirm-password-error");

  var passwordRegex = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;
  var usernameRegex = /^[a-zA-Z].*$/;

  function showError(element, message) {
    element.css("display", "block").text(message);
  }

  function hideError(element) {
    element.css("display", "none").text("");
  }

  function validateForm() {
    var username = $.trim($("#username").val());
    var password = $.trim($("#password").val());

    var valid = true;

    if (username === "") {
      showError(usernameError, "ناوی بەکارهێنەر پێویستە پڕبکرێتەوە.");
      valid = false;
    } else if (username.length < 3) {
      showError(
        usernameError,
        "ناوی بەکارهێنەر پێویستە بەلایەنی کەمەوە ٣ پیت بێت."
      );
      valid = false;
    } else if (!usernameRegex.test(username)) {
      showError(usernameError, " ناوی بەکارهێنەر پێویستە بە پیت دەست پێبکات.");
      valid = false;
    } else {
      hideError(usernameError);
    }

    if (password === "") {
      showError(passwordError, "وشەی نهێنی پێویستە پڕبکرێتەوە.");
      valid = false;
    } else if (!passwordRegex.test(password)) {
      showError(
        passwordError,
        "وشەی نهێنی پێویستە بەلایەنی کەمەوە ٨ پیت بێت و بەلایەنی کەمەوە ژمارەیەک و پیتێک و هێمایەکی تێدابێت."
      );
      valid = false;
    } else {
      hideError(passwordError);
    }

    if (form.attr("id") === "register-form") {
      var confirmPassword = $.trim($("#confirm_password").val());

      if (confirmPassword === "") {
        showError(
          confirmPasswordError,
          "وشەی نهێنی دڵنیایی پێویستە پڕبکرێتەوە."
        );
        valid = false;
      } else if (password !== confirmPassword) {
        showError(
          confirmPasswordError,
          "وشەی نهێنی و وشەی نهێنیی دڵنیایی وەک یەک نین."
        );
        valid = false;
      } else {
        hideError(confirmPasswordError);
      }
    }

    return valid;
  }

  form.submit(function () {
    return validateForm();
  });

  // show password checkbox functionality
  var showPasswordCheckbox = $("#show-password");
  function togglePasswordVisibility() {
    var form = $("form");
    var password = $("#password");

    if (showPasswordCheckbox.prop("checked")) {
      password.prop("type", "text");
    } else {
      password.prop("type", "password");
    }

    if (form.attr("id") == "register-form") {
      var confirm_password = $("#confirm_password");

      if (showPasswordCheckbox.prop("checked")) {
        confirm_password.prop("type", "text");
      } else {
        confirm_password.prop("type", "password");
      }
    }
  }

  showPasswordCheckbox.click(function () {
    togglePasswordVisibility();
  });
});
