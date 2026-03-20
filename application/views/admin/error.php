<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / Error</div>
			</div>
            <div class="card-body">
				<div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        	</div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>
  