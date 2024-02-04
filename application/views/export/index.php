<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>Export</div>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('export'); ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mr-3">
					<label for="search_from" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				  </div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="search_to" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				  </div>
				  <button type="submit" name="submit" value="usage" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>
				<br>
				<?php if($bills): ?>
				<table class="table">
    			<thead>
					<tr>
						<th><?php echo $this->lang->line('invoices'); ?></th>
						<th>total_net</th>
						<th>total_brut</th>
						<th>options</th>
					</tr>
				</thead>
				<tbody>
                    <tr>
                        <td><?php echo $bills['invoices']; ?></td>
                        <td><?php echo number_format($bills['total'], 2, ",", " "); ?></td>
                        <td><?php echo number_format($bills['total_brut'], 2, ",", " "); ?></td>
                        <td>
                            <a href="<?php echo base_url('export/pdf/' . $search_from . '/' . $search_to); ?>" class="btn btn-outline-primary"><i class="fa-regular fa-file-pdf"></i> PDF</a>
                            <a href="<?php echo base_url('export/kluwer/' . $search_from . '/' . $search_to); ?>" class="btn btn-outline-success" download="kluwer_<?php echo $search_from; ?>_<?php echo $search_from; ?>.xml"><i class="fa-regular fa-file-code"></i> Expert/M</a>
                            <a href="<?php echo base_url('export/csv/' . $search_from . '/' . $search_to); ?>" class="btn btn-outline-info" download="csv_<?php echo $search_from; ?>_<?php echo $search_from; ?>.csv"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        </td>
                    </tr>
    			</tbody>
					</table>
                    <?php if($checks != 0): ?>
                    <div class="alert alert-danger" role="alert">Found <i><?php echo $checks; ?> <?php echo $this->lang->line('nota'); ?></i> in this period.</div>
                    <?php endif; ?>
				<?php endif; ?>
                </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>
