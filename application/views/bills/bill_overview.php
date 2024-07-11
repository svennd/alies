<div class="row">
      <div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<?php echo $this->lang->line('title_invoice'); ?>
				<div class="dropdown no-arrow">
					<?php if($this->ion_auth->in_group("admin")): ?>
						<a href="<?php echo base_url('export'); ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-export"></i> export</a>
					<?php endif; ?>
				</div>	
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('invoice/index'); ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="search_from" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo $search_from; ?>" min="<?php echo $max_search_from; ?>" id="search_from">
				</div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="search_to" class="sr-only">search_to</label>
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
					<?php if ($bill['invoice_id']): ?>
						<td data-sort="<?php echo strtotime($bill['invoice_date']) ?>"><?php echo user_format_date($bill['invoice_date'], $user->user_date); ?></td>
					<?php else: ?>
						<td data-sort="<?php echo strtotime($bill['created_at']) ?>"><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
					<?php endif; ?>
					<td>
						<?php if ($bill['invoice_id']): ?>
							<a href="<?php echo base_url('invoice/get_bill/' . $bill['id']); ?>"><?php echo get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']); ?></a>
						<?php endif; ?>
						<?php if($this->ion_auth->in_group("admin") && $bill['modified']): ?>
							<i class="fa-solid fa-skull-crossbones" data-toggle="tooltip" data-placement="top" title="modified"></i>
						<?php endif;?>
					</td>
					<td data-sort="<?php echo $bill['total_brut']; ?>">
							<?php
								$total 	= floatval($bill['total_brut']);
								$cash 	= floatval($bill['cash']);
								$card 	= floatval($bill['card']);
								$transfer = floatval($bill['transfer']);
							?>

							<?php if($total == $card): ?>
                                <?php echo $total . " &euro; " ?>
                            <?php elseif($total == $cash): ?>
                                <?php echo "<i class='fa-solid fa-money-bill' style='color:green'></i> " . $total . "&euro; " ?>
                            <?php elseif($total == $transfer): ?>
                                <?php echo "<i class='fa-solid fa-fw fa-money-bill-transfer' style='color:tomato'></i> " . $total . "&euro; " ?>
                            <?php else: ?>
                                <?php echo $total . " &euro;"; ?><br/>
                                <small>
									<?php echo ($card != 0) ? "<i class='fab fa-cc-visa' style='color:blue'></i> " . $card . "&euro; " : ""; ?>
									<?php echo ($cash != 0) ? "<i class='fa-solid fa-money-bill' style='color:green'></i> " . $cash . "&euro; " : ""; ?>
									<?php echo ($transfer != 0) ? "<i class='fa-solid fa-fw fa-money-bill-transfer' style='color:tomato'></i> " . $transfer . "&euro; " : ""; ?>
								</small>
                            <?php endif; ?>
					</td>
					<td><a href="<?php echo base_url('owners/detail/' . $bill['owner']['user_id']); ?>"><?php echo $bill['owner']['last_name']; ?></a>
						(<?php echo $bill['owner']['user_id']; ?>)
					</td>
					<td data-sort="<?php echo $bill['status']; ?>">
						<?php
							if (in_array($bill['status'], array(BILL_PAID, BILL_HISTORICAL)))
							{
								if ($transfer != 0 && $bill['transfer_verified'] == 1 || $transfer == 0)
								{
									$state = PAYMENT_PAID;
									$color = 'btn-outline-success';
									$icon = '<i class="fa-regular fa-fw fa-circle-check"></i>';
								}
								else
								{
									$state = PAYMENT_PROCESSING;
									$color = 'btn-outline-warning';
									$icon = '<i class="fa-solid fa-fw fa-gear fa-spin"></i>';
								}
							}
							else
							{
								$state = PAYMENT_UNPAID;
								$color = 'btn-outline-danger';
								$icon = '<i class="fa-regular fa-fw fa-circle-xmark"></i>';
							}
						?>
						<a href="<?php echo base_url('invoice/get_bill/' . $bill['id']); ?>" target="_blank" class="btn btn-sm <?php echo $color; ?>"><?php echo $icon; ?></a>
						<?php if($state == PAYMENT_PROCESSING): ?>
							<a href="<?php echo base_url('invoice/verify/' . $bill['id']); ?>" class="btn btn-sm btn-outline-primary ml-5"><i class="fa-regular fa-circle-check"></i> verify</a>
						<?php endif; ?>
					</td>
					<td data-sort="<?php echo $bill['vet']['id']; ?>"><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>
					<?php if($this->ion_auth->in_group("admin")): ?>
						<td data-search="<?php echo (($transfer != 0) ? 1 : "0") . $bill['transfer_verified']; ?>"><a href='<?php echo base_url('admin_invoice/edit_bill/' . $bill['id']); ?>' class="btn btn-outline-danger btn-sm">edit</a></td>
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
const USER_ID = <?php echo $this->user->id; ?>;
const VET_COLUMN = 5;

document.addEventListener("DOMContentLoaded", function(){

	$("#invoice").addClass('active');
	var table = $("#dataTable").DataTable({
		responsive: true,
		pageLength: 250,
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
			<?php if($this->ion_auth->in_group("admin")): ?>
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm'},
			<?php else: ?>
            { text:'<i class="fa-solid fa-fw fa-users"></i>', className:'btn btn-outline-success btn-sm', 
				action: function (e, dt, node, config) { 
					dt.buttons(0).text(dt.buttons(0).text()[0] === '<i class="fa-solid fa-fw fa-users"></i>' ? '<i class="fa-solid fa-fw fa-users-slash"></i>' : '<i class="fa-solid fa-fw fa-users"></i>');
					node[0].className = (node[0].className === 'btn btn-outline-success btn-sm' ? 'btn btn-outline-warning btn-sm' : 'btn btn-outline-success btn-sm');
					toggleHiddenRows(VET_COLUMN, USER_ID);
				}
			}
			<?php endif; ?>
        ],
		"order": [[0, 'desc']]
	});

	<?php if(!$this->ion_auth->in_group("admin")): ?>
		toggleHiddenRows(VET_COLUMN, USER_ID);
	<?php endif; ?>


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
