<div class="row">
      <div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<?php echo $this->lang->line('title_invoice'); ?>
				<div class="dropdown no-arrow">
					<?php if($this->ion_auth->in_group("admin")): ?>
						<a href="<?php echo base_url(); ?>export/facturen/<?php echo $search_from; ?>/<?php echo $search_to; ?>" class="btn btn-outline-info btn-sm" download><i class="fas fa-file-export"></i> xml export</a>
					<?php else: ?>
						<a href="#" id="toggleAll" role="button" class="btn btn-outline-success btn-sm">
							<i class="fa-solid fa-users"></i><span>&nbsp;<?php echo $this->lang->line('AllVets'); ?></span>
						</a>
					<?php endif; ?>
				</div>	
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>invoice/index" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo $search_from; ?>" min="<?php echo $max_search_from; ?>" id="search_from">
				</div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control" value="<?php echo $search_to; ?>" max="<?php echo date_format(new DateTime(), 'Y-m-d'); ?>" id="search_to">
				  </div>
				  <button type="submit" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>

				<br/>
			<?php if ($bills): ?>

				<table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('date'); ?></th>
					<th><?php echo $this->lang->line('invoice_id'); ?></th>
					<th><?php echo $this->lang->line('amount'); ?></th>
					<th><?php echo $this->lang->line('client'); ?></th>
					<th><?php echo $this->lang->line('client_id'); ?></th>
					<th><?php echo $this->lang->line('state'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<?php if($this->ion_auth->in_group("admin")): ?>
						<th>edit</th>
					<?php endif; ?>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($bills as $bill): ?>
				<tr>
					<td data-sort="<?php echo strtotime($bill['created_at']) ?>">
						<?php echo user_format_date($bill['created_at'], $user->user_date); ?><br/>
						<!-- <small><?php echo timespan(strtotime($bill['created_at']), time(), 1); ?> Ago -->
					</td>
					<td>
						<?php echo $bill['invoice_id']; ?>
					</td>
					<td>
						<a href="<?php echo base_url('invoice/get_bill/' . $bill['id']); ?>"><?php echo $bill['total_brut']; ?> &euro;</a>
						<?php if($this->ion_auth->in_group("admin") && $bill['modified']): ?>
							<i class="fa-solid fa-skull-crossbones" data-toggle="tooltip" data-placement="top" title="modified"></i>
						<?php endif;?>
					</td>
					<td><?php echo $bill['owner']['user_id']; ?></td>		
					<td><a href="<?php echo base_url('owners/detail/' . $bill['owner']['user_id']); ?>"><?php echo $bill['owner']['last_name']; ?></a></td>
					<td>
						<a href="<?php echo base_url('invoice/get_bill/' . $bill['id']. '/1'); ?>" target="_blank" class="btn btn-sm <?php echo ($bill['status'] == BILL_PAID) ? 'btn-outline-success' : 'btn-outline-danger'; ?>">
							<?php echo ($bill['status'] == BILL_PAID) ? '<i class="fa-regular fa-fw fa-circle-check"></i> ' . $this->lang->line('payed') : '<i class="fa-regular fa-circle-xmark"></i> ' . $this->lang->line('payment_incomplete'); ?>
						</a>
					</td>
					<td data-sort="<?php echo $bill['vet']['id']; ?>"><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>
					<?php if($this->ion_auth->in_group("admin")): ?>
					<td><a href='<?php echo base_url('admin_invoice/edit_bill/' . $bill['id']); ?>' class="btn btn-outline-danger btn-sm">edit</a></td>
					<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No bills in this view
			<?php endif; ?>
                </div>
		</div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#invoice").addClass('active');
	var table = $("#dataTable").DataTable({		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm'},
            { extend:'pdf', text:'<i class="far fa-file-pdf"></i> PDF', className:'btn btn-outline-success btn-sm'}
        ],"order": [[0, 'desc']]}
	
	);

	<?php if(!$this->ion_auth->in_group("admin")): ?>
	toggleHiddenRows(4, <?php echo $this->user->id; ?>);
	<?php endif; ?>

	$('#toggleAll').on('click', function() {
		toggleHiddenRows(4, <?php echo $this->user->id; ?>);

		$(this).toggleClass('btn-outline-danger btn-outline-success');
		if ($(this).hasClass('btn-outline-success')) {
        	$(this).html('<i class="fa-solid fa-fw fa-users"></i> <?php echo $this->lang->line('AllVets'); ?>');
        } else {
			$(this).html('<i class="fa-solid fa-fw fa-users-slash"></i> <?php echo $this->lang->line('OnlyMe'); ?>');
        }
	});

	function toggleHiddenRows(field, input_value) {

        table.rows().every(function() {
          var value = this.data()[field]; 
		  var sortValue = value['@data-sort']; 
		  
          if (parseInt(sortValue) !== parseInt(input_value)) {
            var row = this.nodes().to$();

            if (row.is(":hidden")) {
              row.show();
            } else {
              row.hide();
            }
          }
        });
      }

	$('[data-toggle="tooltip"]').tooltip();
});
</script>
