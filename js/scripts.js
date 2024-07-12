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
});
