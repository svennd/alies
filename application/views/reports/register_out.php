<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('Reporting'); ?> / <?php echo $this->lang->line('Reg_out'); ?></div>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>reports/register_out/" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mr-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				  </div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				  </div>
				  <button type="submit" name="submit" value="usage" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>
				<?php if($register_out): ?>
				<br>
				<table class="table" id="dataTable">
    			<thead>
					<tr>
						<th><?php echo $this->lang->line('name'); ?></th>
						<th><?php echo $this->lang->line('volume'); ?></th>
						<th><?php echo $this->lang->line('lotnr'); ?></th>
						<th><?php echo $this->lang->line('date'); ?></th>
						<th><?php echo $this->lang->line('price_total_sale'); ?></th>
						<th><?php echo $this->lang->line('price_alies'); ?></th>
						<th><?php echo $this->lang->line('price_dayprice'); ?></th>
						<th><?php echo $this->lang->line('vhbcode'); ?></th>
						<th><?php echo $this->lang->line('type'); ?></th>
						<th><?php echo $this->lang->line('booking'); ?></th>
						<th><?php echo $this->lang->line('btw_sell'); ?></th>
						<th><?php echo $this->lang->line('vet'); ?></th>
						<th><?php echo $this->lang->line('location'); ?></th>
						<th><?php echo $this->lang->line('pet_info'); ?></th>
						<th><?php echo $this->lang->line('client'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($register_out as $us): 
							$buy_volume = ($us['buy_volume'] == 0) ? 1 : $us['buy_volume'];
					?>
					<tr>
						<td><?php echo $us['product_name']; ?></td>
						<td><?php echo $us['volume']; ?> <?php echo $us['unit_sell']; ?></td>
						<td><?php echo $us['lotnr']; ?></td>
						<td data-sort="<?php echo strtotime($us['event_date']); ?>"><?php echo user_format_date($us['event_date'], $user->user_date); ?></td>
						<td><?php echo $us['total_sell_price']; ?> &euro;</td>
						<td><?php echo round(($us['buy_price']/$buy_volume)*$us['volume'], 2); ?> &euro;</td>
						<td><?php echo round(($us['in_price_test']/$buy_volume)*$us['volume'], 2); ?> &euro;</td>
						<td><?php echo (empty($us['vhbcode'])) ? '-' : $us['vhbcode']; ?></td>
						<td><?php echo $us['product_type']; ?></td>
						<td><?php echo $us['code'] . ' (' . $us['category'] . ')'; ?></td>
						<td><?php echo $us['btw']; ?>%</td>
						<td><?php echo $us['vet_name']; ?></td>
						<td><?php echo $us['stock_name']; ?></td>
						<td><a href="<?php echo base_url('pets/fiche/' . $us['pet_id']); ?>"><?php echo $us['pet_id']; ?></a></td>
						<td><a href="<?php echo base_url('owners/detail/' . $us['owner_id']) ?>"><?php echo $us['owner_id']; ?></a></td>
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
	$("#reportsmgm").addClass('active');
	$("#rep").show();
	$("#reg_out").addClass('active');
	$("#dataTable").DataTable({
		responsive: true,
		// dom: 'Bfrtip',
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            // { extend:'csv', text:'<i class="fas fa-file-csv"></i> CSV', className:'btn btn-outline-success btn-sm'},
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm'},
            // { extend:'pdf', text:'<i class="far fa-file-pdf"></i> PDF', className:'btn btn-outline-success btn-sm'}
        ]
	});
});
</script>
