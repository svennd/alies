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
          <a class="btn btn-primary" href="<?php echo base_url('auth/logout'); ?>">Logout</a>
        </div>
      </div>
    </div>
  </div>

	<!-- Bootstrap core JavaScript-->
	<script src="<?php echo base_url('vendor/components/jquery/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('vendor/components/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?php echo javascript('assets/js/alies.js'); ?>"></script>

	<!-- datatables -->
	<script src="<?php echo javascript('assets/js/datatables.min.js'); ?>"></script>
	<script src="<?php echo javascript('assets/js/datatables.plugin.min.js'); ?>"></script>

	<!-- select -->
	<script src="<?php echo base_url('vendor/select2/select2/dist/js/select2.min.js'); ?>"></script>

	<!-- select -->
  <script src="<?php echo base_url('node_modules/sweetalert2/dist/sweetalert2.all.min.js'); ?>"></script>

  <!-- inputmask -->
  <script src="<?php echo base_url('vendor/robinherbots/jquery.inputmask/dist/jquery.inputmask.min.js'); ?>"></script>

  <!-- default config -->
  <script src="<?php echo javascript('assets/js/settings.js'); ?>"></script>

  <?php if (date('m') == 4 && date('d') == 1): ?>
    <!-- april fish -->
    <script>
      const BASE_URL = '<?php echo base_url(); ?>';
    </script>
    <script src="<?php echo javascript('assets/js/april_fish.js'); ?>"></script>
    <div class="work-in-progress"><i class="fa-solid fa-fish fa-flip"></i></div> 
  <?php endif; ?>
  
	<?php echo (isset($extra_footer)) ? $extra_footer : ""; ?>

<script>
  const URL_ADD_STICKY 	= '<?php echo base_url('sticky/add'); ?>';
  const LANG_PLACEHOLDER = '<?php echo $this->lang->line('type_your_sticky'); ?>';
  const LANG_STICKY_SAVED = '<?php echo $this->lang->line('sticky_saved'); ?>';
</script>
</body>

</html>