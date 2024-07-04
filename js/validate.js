$(document).ready(function () {
  // form validation
  var form = $("form");

  function validateForm() {
    var username = $.trim($("#username").val());
    var password = $.trim($("#password").val());

    var usernameError = $("#username-error");
    var passwordError = $("#password-error");
    var confirmPasswordError = $("#confirm-password-error");

    var passwordRegex = new RegExp(
      "^(?=.*\\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,}$"
    );
    var usernameRegex = new RegExp("^[a-zA-Z].*$");

    if (username === "" && password === "") {
      usernameError.text("ناوی بەکارهێنەر پێویستە پڕبکرێتەوە.");
      passwordError.text("وشەی نهێنی پێویستە پڕبکرێتەوە.");
      return false;
    } else {
      if (username === "") {
        passwordError.text("");
        usernameError.text("ناوی بەکارهێنەر پێویستە پڕبکرێتەوە.");
        return false;
      } else {
        usernameError.text("");
      }
      if (password === "") {
        usernameError.text("");
        passwordError.text("وشەی نهێنی پێویستە پڕبکرێتەوە.");
        return false;
      } else {
        passwordError.text("");
      }
    }

    if (form.attr("id") == "register-form") {
      var confirm_password = $.trim($("#confirm_password").val());

      if (confirm_password === "") {
        confirmPasswordError.text("وشەی نهێنی دڵنیایی پێویستە پڕبکرێتەوە.");
        return false;
      } else {
        confirmPasswordError.text("");
      }

      if (password !== confirm_password) {
        confirmPasswordError.text(
          "وشەی نهێنی و وشەی نهێنیی دڵنیایی وەک یەک نین."
        );
        return false;
      } else {
        confirmPasswordError.text("");
      }
    }

    if (username.length < 3) {
      usernameError.text("ناوی بەکارهێنەر پێویستە بەلایەنی کەمەوە ٣ پیت بێت.");
      return false;
    } else {
      usernameError.text("");
    }

    if (!passwordRegex.test(password)) {
      passwordError.text(
        "وشەی نهێنی پێویستە بەلایەنی کەمەوە ٨ پیت بێت و بەلایەنی کەمەوە ژمارەیەک و پیتێک و هێمایەکی تێدابێت."
      );
      return false;
    } else {
      passwordError.text("");
    }

    if (!usernameRegex.test(username)) {
      usernameError.text(" ناوی بەکارهێنەر پێویستە بە پیت دەست پێبکات.");
      return false;
    } else {
      usernameError.text("");
    }
    return true;
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
