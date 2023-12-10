     </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>A <a href="https://www.svennd.be" target="_blank">SvennD</a> Creation; <a href="https://github.com/svennd/alies/issues" target="_blank">Issues</a></span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="<?php echo base_url() . 'auth/logout'?>">Logout</a>
        </div>
      </div>
    </div>
  </div>

	<!-- Bootstrap core JavaScript-->
	<script src="<?php echo base_url(); ?>vendor/components/jquery/jquery.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/components/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?php echo base_url(); ?>assets/js/sb-admin-2.js"></script>

	<!-- datatables -->
	<script src="<?php echo base_url(); ?>assets/js/datatables.min.js"></script>

	<!-- select -->
	<script src="<?php echo base_url(); ?>vendor/select2/select2/dist/js/select2.min.js"></script>

	<!-- select -->
  <script src="<?php echo base_url(); ?>assets/js/sweetalert2.all.min.js"></script>

	<?php echo (isset($extra_footer)) ? $extra_footer : ""; ?>

  <script>
document.addEventListener("DOMContentLoaded", function() {


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

$('#sticky_messages').on('click', function () {
  Swal.fire({
  input: 'textarea',
  inputLabel: 'Sticky Note',
  inputPlaceholder: '<?php echo $this->lang->line('type_your_sticky'); ?>',
  showLoaderOnConfirm: true,
  showCancelButton: true,

  preConfirm: (data_field_input) => {
        $.ajax({
          method: 'POST',
          url: '<?php echo base_url('sticky/add'); ?>',
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
        title: '<?php echo $this->lang->line('sticky_saved'); ?>',
        showConfirmButton: true,
        timer: 1500
      });
    }
  
  });

}); 
}); 


<?php if ($this->ion_auth->in_group("accounting")): ?>

  var Trail = function(options) {
  this.size        = options.size || 50;
  this.trailLength = options.trailLength || 20;
  this.interval    = options.interval || 15;
  this.hueSpeed    = options.hueSpeed || 6;

  this.boxes = [];
  this.hue   = 0;
  this.mouse = {
    x : window.innerWidth/2,
    y : window.innerHeight/2
  };

  this.init = function() {
    // console.log('fire');
    for (var i = 0; i < this.trailLength; i++) {
      this.boxes[i]              = document.createElement('div');
      this.boxes[i].className    = 'box';
      this.boxes[i].style.width  = this.size + 'px';
      this.boxes[i].style.height = this.size + 'px';
      document.body.appendChild(this.boxes[i]);
    }

    var self = this;

    // document.onmousemove = function() {
    //   event = event || window.event;
    //   self.mouse.x = event.clientX;
    //   self.mouse.y = event.clientY;
    //   console.log(event);
    // };

    //Periodically update mouse tracing and boxes
    setInterval(function(){
      self.updateHue();
      self.updateBoxes();
    }, this.interval);
  }

  //Update hue and constrain to 360
  this.updateHue = function() {
    this.hue = (this.hue + this.hueSpeed) % 360;
  }

  //Update box positions and stylings
  this.updateBoxes = function() {
    for (var i = 0; i < this.boxes.length; i++) {
      if (i+1 === this.boxes.length) {
        this.boxes[i].style.top             = this.mouse.y - this.size/2 + 'px';
        this.boxes[i].style.left            = this.mouse.x - this.size/2 + 'px';
        this.boxes[i].style.backgroundColor = 'hsl(' + this.hue + ', 90%, 50%)';
      } else {
        this.boxes[i].style.top             = this.boxes[i+1].style.top;
        this.boxes[i].style.left            = this.boxes[i+1].style.left;
        this.boxes[i].style.backgroundColor = this.boxes[i+1].style.backgroundColor;
      }
    }
  }
}

var options = {
  trailLength: 15,
  size: 5,
  interval: 10,
  hueSpeed: 2
};
// console.log("init");
var trail = new Trail(options);
trail.init();

//Hotfix
document.onmousemove = function() {
  trail.mouse.x = event.clientX+15;
  trail.mouse.y = event.clientY+15;
};

<?php endif; ?>
  </script>

<div class="work-in-progress"><i class="fa-solid fa-person-digging"></i></div>
</body>

</html>