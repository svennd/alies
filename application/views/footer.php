     </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>A <a href="https://www.svennd.be" target="_blank">SvennD</a> Creation; <a href="https://github.com/svennd/alies/issues" target="_blank">Issues</a> & <a href="https://docs.google.com/document/d/1xZcrtoC5dQdrqQb-Q0UNQrPTMVbtqWo6GumkxTh9Lxc/edit?usp=sharing" target="_blank">Docs</a></span>
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
	<script src="<?php echo base_url(); ?>assets/js/jQuery.3.4.1.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?php echo base_url(); ?>assets/js/sb-admin-2.js"></script>
	
	<!-- datatables -->
	<script src="<?php echo base_url(); ?>assets/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/dataTables.bootstrap4.min.js"></script>
		
	<!-- select -->
	<script src="<?php echo base_url(); ?>assets/js/select2.min.js"></script>
	
	<!-- time based stuff -->
	<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>
	
	<?php echo (isset($extra_footer)) ? $extra_footer : ""; ?>
</body>

</html>
