(function($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
		    $("body").toggleClass("sidebar-toggled");
	localStorage.setItem("sidebar", 0);
      $(".sidebar").toggleClass("toggled");

    if ($(".sidebar").hasClass("toggled")) {
		localStorage.setItem("sidebar", 1);
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // for phones 
  if ($(window).width() < 450) {  
    $('.sidebar .collapse').collapse('hide');
    $(".sidebar").toggleClass("toggled");
  };

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // spielerei
  $('.shakeit').hover(
    function() {
      $(this).find('i').addClass('fa-shake');
    },
    function() {
      $(this).find('i').removeClass('fa-shake');
    }
  );
  $('.beatfade').hover(
    function() {
      $(this).find('i').addClass('fa-beat-fade');
    },
    function() {
      $(this).find('i').removeClass('fa-beat-fade');
    }
  );
  $('.bounceit').hover(
    function() {
      $(this).find('i').addClass('fa-bounce');
    },
    function() {
      $(this).find('i').removeClass('fa-bounce');
    }
  );
  
// Sticky Note
$('#sticky_messages').on('click', function () {
  Swal.fire({
  input: 'textarea',
  inputLabel: 'Sticky Note',
  inputPlaceholder: LANG_PLACEHOLDER,
  showLoaderOnConfirm: true,
  showCancelButton: true,
    preConfirm: (data_field_input) => {
          $.ajax({
            method: 'POST',
            url: URL_ADD_STICKY,
            data: {
              content: `${data_field_input}`,
              private: 0
            },
            error: function(xhr, status, error) {
              // handle error
              Swal.fire({
                title: 'Error',
                text: 'An error occurred.',
                icon: 'error'
              });
            }
          });
    },
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        icon: 'success',
        title: LANG_STICKY_SAVED,
        showConfirmButton: true,
        timer: 1500
      });
    }
  });
}); 



})(jQuery); // End of use strict
