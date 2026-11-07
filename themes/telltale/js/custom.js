jQuery(document).ready(function($) {
  /**
   * Insert comment reply below parent
   * Toggle if already visible
   */
  $(document)
    .on("click", ".js-comment-reply", function(event) {
      event.preventDefault();

      var $elem = $(this);
      var $parent = $(this).closest(".Item");
      var $target = $elem.data("target");
      var href = $elem.attr("href");

      if ($target && $target.length) {
        $target.slideToggle("fast");
        return true;
      }

      $.ajax(href, {
        data: {
          DeliveryType: "VIEW",
          DeliveryMethod: "JSON",
          TransientKey: gdn.definition("TransientKey")
        },
        dataType: "json"
      }).then(function(json) {
        gdn.inform(json);
        $target = $(json.Data);
        $parent.append($target);

        $elem.data("target", $target);
        $target.trigger("contentLoad");
      });
    })
    .on("click", ".js-comment-preview", function(event) {
      var commentID = $(this).data("id");
      var commentItem = $("#Comment_" + commentID);

      // If comment Item is visible on the page
      if (commentItem.length) {
        event.preventDefault();

        var docViewTop = $(window).scrollTop();
        var docViewBottom = docViewTop + $(window).height();
        var elementTop = commentItem.offset().top;
        var elementBottom = elementTop + commentItem.height();
        var isVisible =
          elementBottom <= docViewBottom && elementTop >= docViewTop;

        if (!isVisible) {
          window.scrollTo(0, elementTop - 50);
        }

        commentItem.effect("highlight", {}, "slow");
      }
    })
    .on("click", ".js-reveal-next", function(event) {
      event.preventDefault();

      var $this = $(this);
      $this.next(".hidden").removeClass("hidden").attr("aria-hidden", "false");
      $this.remove();
    });

  // Highlight directly linked comments
  if (window.location.hash && window.location.hash.indexOf("#Comment_") === 0) {
    var commentItem = $(window.location.hash);
    if (commentItem.length) {
      commentItem.delay(900).effect("highlight", {}, "slow");
    }
  }

  jQuery.fn.extend({
    accessibleFlyout: function(isOpen) {
      // Override accessibleFlyout in global.js to stay visible on mobile

      $this = $(this);
      $this.attr("aria-hidden", (!isOpen).toString());

      if (isOpen) {
        var rightOffset =
          $(window).width() - ($this.offset().left + $this.outerWidth(true));

        if (rightOffset < 0) {
          $this.css({ left: rightOffset - 4 });
        }
      }
    }
  });
});
