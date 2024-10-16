<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('Reporting'); ?> / Revenue</div>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('reports/revenue'); ?>" method="post" autocomplete="off" class="form-inline">

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
				<?php if($revenue): ?>
				<br>

				<table class="table" id="dataTable">
    			<thead>
					<tr>
						<th>month</th>
						<th>bills</th>
						<th>total_net</th>
						<th>total_brut</th>
						<th>options</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($revenue as $us): 
                        $date = str_pad($us['m'], 2, '0', STR_PAD_LEFT) . '/01/' . $us['y'];
                        $date_int = strtotime($date);

						$date_from = (($search_from) ? $search_from : date("Y-m-d", $date_int));
						$date_to = (($search_to) ? $search_to : date("Y-m-t", $date_int));
                        ?>
					<tr>
						<td data-sort="<?php echo $date_int; ?>"><?php echo date("M 'y", $date_int); ?></td>
						<td><a href="<?php echo base_url('invoice/index/' . $date_int . '/' . strtotime(date("Y-m-t", $date_int))); ?>"><?php echo $us['invoices']; ?></a></td>
						<td><?php echo number_format($us['total'], 2, ",", " "); ?></td>
						<td><?php echo number_format($us['total_brut'], 2, ",", " "); ?></td>
						<td>
							<a href="<?php echo base_url('export/kluwer/'. $date_from . '/'. $date_to); ?>" class="btn btn-sm btn-outline-success" download="kluwer_<?php echo $date_to ?>.xml"><i class="fa-solid fa-file-export"></i> Kluwer</a>
						</td>
					</tr>
					<?php endforeach; ?>
    			</tbody>
					</table>
				<?php endif; ?>
                </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	// $("#rep").show();
	// $("#reg_in").addClass('active');
	$("#dataTable").DataTable({
		responsive: true
	});
});
</script>
