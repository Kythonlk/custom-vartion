console.log("Js file is working");

document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("variantModal");
  var btn = document.getElementById("selectVariantBtn");
  var close = document.getElementsByClassName("close")[0];
  var brandSelector = document.getElementById("brandSelector");
  var colorSelector = document.getElementById("colorSelector");

  btn.onclick = function () {
    modal.style.display = "block";
  };

  close.onclick = function () {
    modal.style.display = "none";
  };

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };

  var searchInput = document.createElement("input");
  searchInput.type = "text";
  searchInput.id = "colorSearch";
  searchInput.placeholder = "Search colors...";
  brandSelector.parentNode.insertBefore(searchInput, brandSelector.nextSibling);

  brandSelector.onchange = function () {
    var brandName = this.value;

    jQuery.ajax({
      url: "/wp-admin/admin-ajax.php",
      type: "POST",
      data: {
        action: "load_colors",
        brand_name: brandName,
      },
      success: function (response) {
        let allColors = response;

        function updateDisplayedColors(searchText) {
          colorSelector.innerHTML = "";
          allColors.forEach(function (color) {
            if (
              color.colour_name
                .toLowerCase()
                .includes(searchText.toLowerCase()) ||
              searchText === ""
            ) {
              var colorCard = document.createElement("div");
              colorCard.classList.add("color-card");

              var colorNameDiv = document.createElement("h5");
              colorNameDiv.innerText = "Color : " + color.colour_name;
              colorNameDiv.classList.add("color-name");

              var codeDiv = document.createElement("p");
              codeDiv.innerText = "Color Code : " + color.code;
              codeDiv.classList.add("color-code");

              var stock_codeDiv = document.createElement("p");
              stock_codeDiv.innerText = "Stock Code : " + color.stock_code;
              stock_codeDiv.classList.add("stock-code");

              var colorImageDiv = document.createElement("img");
              colorImageDiv.src = color.chip;
              colorImageDiv.classList.add("color-image");
              colorImageDiv.style.width = "300px";
              colorImageDiv.style.height = "150px";

              colorCard.appendChild(colorNameDiv);
              colorCard.appendChild(codeDiv);
              colorCard.appendChild(stock_codeDiv);
              colorCard.appendChild(colorImageDiv);

              colorCard.style.cursor = "pointer";
              colorCard.onclick = function () {
                selectedColor = color.colour_name;
                updateOrCreateHiddenInput(
                  addToCartForm,
                  "selected_color",
                  selectedColor,
                );
                document.getElementById("variantModal").style.display = "none";
              };

              colorSelector.appendChild(colorCard);
            }
          });
        }

        updateDisplayedColors("");

        document
          .getElementById("colorSearch")
          .addEventListener("input", function () {
            updateDisplayedColors(this.value);
          });
      },
    });
  };

  brandSelector.dispatchEvent(new Event("change"));

  var addToCartForm = document.querySelector("form.cart");
  addToCartForm.addEventListener("submit", function () {
    console.log(
      "Submitting with brand and color:",
      brandSelector.value,
      selectedColor,
    );
    updateOrCreateHiddenInput(
      addToCartForm,
      "selected_brand",
      brandSelector.value,
    );
    updateOrCreateHiddenInput(addToCartForm, "selected_color", selectedColor);
  });
});

function updateOrCreateHiddenInput(form, name, value) {
  console.log("Updating input:", name, value);
  var existingInput = form.querySelector('input[name="' + name + '"]');
  if (existingInput) {
    existingInput.value = value;
  } else {
    var input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }
}

console.log("End of script");
