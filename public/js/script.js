// Custom JavaScript for any interactivity, if needed.
// For now, Bootstrap's JS handles the navbar toggler.
// You can add more jQuery or vanilla JS here for other features.

const $ = window.jQuery // Declare the $ variable

$(document).ready(() => {
  // Example: Smooth scrolling for anchor links (if you add any)
  $('a[href*="#"]:not([href="#"])').on("click", function (event) {
    if (
      location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") &&
      location.hostname == this.hostname
    ) {
      var target = $(this.hash)
      target = target.length ? target : $("[name=" + this.hash.slice(1) + "]")
      if (target.length) {
        event.preventDefault()
        $("html, body").animate(
          {
            scrollTop: target.offset().top - 72, // Adjust for fixed header height
          },
          1000,
        )
        return false
      }
    }
  })
})
