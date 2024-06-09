$(document).ready(function () {
  // form validation
  var form = $("form");
  function validateForm() {
    var username = $.trim($("#username").val());
    var password = $.trim($("#password").val());

    var passwordRegex = new RegExp(
      "^(?=.*d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,}$"
    );
    var usernameRegex = new RegExp("^[a-zA-Z].*$");

    if (username === "" || password === "") {
      alert("تکایە هەموو خانەکان پڕبکەوە.");
      return false;
    }

    if (form.attr("id") == "register-form") {
      var confirm_password = $.trim($("#confirm_password").val());

      if (confirm_password === "") {
        alert("تکایە هەموو خانەکان پڕبکەوە.");
        return false;
      }

      if (password !== confirm_password) {
        alert("وشەی نهێنی و وشەی نهێنیی دڵنیایی وەک یەک نین.");
        return false;
      }
    }

    if (username.length < 3) {
      alert("ناوی بەکارهێنەر پێویستە بەلایەنی کەمەوە ٣ پیت بێت.");
      return false;
    }

    if (!passwordRegex.test(password)) {
      alert(
        "وشەی نهێنی پێویستە بەلایەنی کەمەوە ٨ پیت بێت و بەلایەنی کەمەوە ژمارەیەک و پیتێک و هێمایەکی تێدابێت."
      );
      return false;
    }

    if (!usernameRegex.test(username)) {
      alert("ناوی بەکارهێنەر پێویستە بە پیت دەست پێبکات.");
      return false;
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
