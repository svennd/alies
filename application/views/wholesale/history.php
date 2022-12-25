<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <a href="<?php echo base_url('import/import_covetrus'); ?>"><?php echo $this->lang->line('wholesale'); ?></a> / <?php echo $data['vendor_id']; ?></div>
				<div class="dropdown no-arrow">
				</div>
			</div>
            <div class="card-body">

			<?php if ($data): ?>
				<table class="table">
					<tr>
						<td>artikel nr</td>
						<td><?php echo $data['vendor_id']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('description'); ?></td>
						<td><?php echo $data['description']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale'); ?></td>
						<td><?php echo $data['bruto']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('VAT'); ?></td>
						<td><?php echo $data['btw']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale_sell'); ?></td>
						<td><?php echo $data['sell_price']; ?></td>
					</tr>
					<tr>
						<td>distributor</td>
						<td><?php echo $data['distributor']; ?></td>
					</tr>
					<tr>
						<td>CNK</td>
						<td><?php echo $data['CNK']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('vhbcode'); ?></td>
						<td><?php echo $data['VHB']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('lab_update'); ?></td>
						<td><?php echo date_format(date_create($data['updated_at']), $user->user_date); ?><br/><small><?php echo time_ago($data['updated_at']);?></small></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('lab_update'); ?></td>
						<td><?php echo date_format(date_create($data['created_at']), $user->user_date); ?><br/><small><?php echo time_ago($data['created_at']);?></small></td>
					</tr>
				</table>
				<?php if($data['wholesale_prices']): ?>
				<table class="table">
				<thead>
				<tr>
					<th>created_at</th>
					<th><?php echo $this->lang->line('price_wholesale'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($data['wholesale_prices'] as $d): ?>
				<tr>
					<td><?php echo $d['created_at']; ?></td>
					<td><?php echo $d['bruto']; ?> &euro;</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#admin").addClass('active');
});
</script>
  