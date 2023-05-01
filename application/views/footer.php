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



  </script>
</body>

</html>
