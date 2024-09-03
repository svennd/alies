<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>Stats</div>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('stats/update/' . $stat['id']); ?>" method="post" autocomplete="off" class="form">
                    <div class="form-group">
                        <label for="query">Query</label>
                        <textarea class="form-control" id="query" name="query" rows="20"><?php echo $stat['query']; ?></textarea>
                    </div>
                    <button type="submit" name="submit" value="usage" class="btn btn-outline-primary">Update</button>
                    <a href="<?php echo base_url("stats"); ?>" class="btn btn-outline-danger">Return</a>
				</form>
		    </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>