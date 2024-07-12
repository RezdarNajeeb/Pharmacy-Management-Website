$(function () {
  $(window).scroll(function () {
    var header = $("#header");
    var scrollTop = $(this).scrollTop();
    header.toggleClass("fixed", scrollTop > 0);
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

  function handleUpdateAccountModal() {
    var modal = $("#account-modal");
    var updateUserBtn = $("#update-user");
    var closeBtn = $(".close");

    // When click on the updateUserBtn, show the modal
    updateUserBtn.click(function () {
      modal.css("visibility", "visible");
    });

    // When click on the closeBtn, close the modal
    closeBtn.click(function () {
      modal.css("visibility", "hidden");
    });

    // When click anywhere in the document outside the modal, close the modal
    $(window).click(function (event) {
      if (event.target == modal[0]) {
        modal.css("visibility", "hidden");
      }
    });
  }

  handleUpdateAccountModal();

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
