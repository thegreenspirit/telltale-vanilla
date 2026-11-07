jQuery(document).ready(function($) {
  $(document)
    .on("change", ".js-change-avatar-category", function() {
      var url = $(this).closest("form").prop("action");

      $.ajax(url, {
        data: {
          CategoryID: $(this).val(),
          DeliveryType: "VIEW"
        }
      }).then(function(result) {
        $(".avatar-options").replaceWith($(result).find(".avatar-options"));
      });
    })
    .on("click", ".js-select-avatar", function() {
      var $this = $(this);
      console.log("set avatar to ", $this.prop("src"));
      $(".js-new-avatar-url").val($this.prop("src"));
      $this.siblings().removeClass("border-red");
      $this.addClass("border-red");
      $this.closest(".js-change-picture-form").trigger("submit");
    });
  // .on("submit", ".js-change-picture-form", function(event) {
  //   event.preventDefault();

  //   console.log("submit picture form");
  // });
});