<div class="row">
      <div class="col-lg-10 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / 
					<a href="<?php echo base_url('wholesale/index'); ?>"><?php echo $this->lang->line('wholesale'); ?></a> / 
					<?php echo (isset($data['product'])) ? $data['product']['name'] : $data['vendor_id']; ?>
				</div>

				<div>
					<?php if(isset($data['product'])): ?>
						<a href="<?php echo base_url('pricing/prod/' . $data['product']['id']); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-fw fa-euro-sign"></i></a>
					<?php endif; ?>
				</div>

			</div>
            <div class="card-body">

			<?php if ($data): ?>
				<?php 
					$calculated_prct = false;
					if ($data['last_bruto'] != NULL && $data['last_bruto'] != 0 && $data['bruto'] != 0)
					{
						# calculate the different in %
						$calculated_prct = round((($data['bruto'] / $data['last_bruto']) * 100) - 100);
						$procent = ($calculated_prct > 0) ? 
							"<span style='color:tomato'>" . $calculated_prct . "%</span>"
							:
							(
								($calculated_prct == 0 ) ? 
								"" :
								"<span style='color:green'>" . $calculated_prct . "%</span>"
							)
							;
					}
					else { $procent = ""; }
					?>
				<table class="table table-sm">
					<tr>
						<td>Artikel nr</td>
						<td><?php echo $data['vendor_id']; ?></td>
					</tr>
					<?php if(isset($data['product'])): ?>
					<tr>
						<td><?php echo $this->lang->line('name'); ?></td>
						<td><a href="<?php echo base_url('products/profile/'. $data['product']['id']); ?>"><?php echo $data['product']['name']; ?></a></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><?php echo $this->lang->line('description'); ?></td>
						<td><?php echo $data['description']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale'); ?></td>
						<td>
							<?php if($data['last_bruto'] == $data['bruto']): ?>
								<?php echo $data['bruto']; ?>
							<?php else: ?>
								<?php echo $data['last_bruto']; ?> --> <?php echo $data['bruto']; ?> <span style="color:tomato">(<?php echo $procent; ?>)</span> 
								<a href="<?php echo base_url('wholesale/accept/' . $data['id'] . '/history'); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-rotate-right fa-fw"></i></a>
							<?php endif; ?>
							<?php if($data['ignore_change'] == 1): ?>
								<a href="<?php echo base_url('wholesale/unignore/' . $data['id'] . '/history'); ?>" class="btn btn-sm btn-outline-success"><i class="fa-regular fa-eye fa-fw"></i></a>
							<?php else: ?>
								<a href="<?php echo base_url('wholesale/ignore/' . $data['id'] . '/history'); ?>" class="btn btn-sm btn-outline-success"><i class="fa-regular fa-eye-slash fa-fw"></i></a>
							<?php endif; ?>
						</td>
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
					<?php if(!empty($date['VHB'])): ?>
					<tr>
						<td><?php echo $this->lang->line('vhbcode'); ?></td>
						<td><?php echo $data['VHB']; ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><?php echo $this->lang->line('lab_update'); ?></td>
						<td><?php echo date_format(date_create($data['updated_at']), $user->user_date); ?><br/><small><?php echo time_ago($data['updated_at']);?></small></td>
					</tr>
				</table>
			<?php endif; ?>
                </div>
				
		</div>
	</div>

	<div class="col-lg-2 mb-4">
	<div class="card shadow mb-4">
		<div class="card-header d-flex flex-row align-items-center justify-content-between">Price changes</div>
            <div class="card-body">
		<?php if($data['wholesale_prices']): ?>
				<table class="table table-sm">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('created_at'); ?></th>
					<th><?php echo $this->lang->line('price_wholesale'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($data['wholesale_prices'] as $d): ?>
				<tr>
					<td><?php echo date_format(date_create($d['created_at']), $user->user_date); ?></td>
					<td><?php echo $d['bruto']; ?> &euro;</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
	</div>
	</div>
	</div>
      
</div>
<div class="row">
		<!-- deliveries -->
		<div class="col-lg-7 mb-4">
			<div class="card shadow mb-4">
				<div class="card-header d-flex flex-row align-items-center justify-content-between">Deliveries (automatic)</div>
				<div class="card-body">
				<?php if($data['deliveries']): ?>
						<table class="table table-sm" id="deliveries">
						<thead>
						<tr>
							<th>delivery_date</th>
							<th>delivery_nr</th>
							<th>bruto_price</th>
							<th>netto_price</th>
							<th>amount</th>
							<th>lotnr</th>
							<th>due_date</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($data['deliveries'] as $d): ?>
						<tr>
							<td><?php echo user_format_date($d['delivery_date'], $user->user_date); ?></td>
							<td><?php echo $d['delivery_nr']; ?></td>
							<td><?php echo $d['bruto_price']; ?></td>
							<td><?php echo $d['netto_price']; ?></td>
							<td><?php echo $d['amount']; ?></td>
							<td><?php echo $d['lotnr']; ?></td>
							<td>
							<?php 
										echo 
												(strtotime($d['due_date']) < strtotime(date('Y-m-d'))) ? 
													'<span style="color:tomato;"> ' . user_format_date($d['due_date'], $user->user_date) . '</span>'
														: 
														user_format_date($d['due_date'], $user->user_date)
										; ?>		
							</td>
						</tr>
						<?php endforeach; ?>
						</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-lg-5 mb-4">
			<div class="card shadow mb-4">
				<div class="card-header d-flex flex-row align-items-center justify-content-between">Deliveries (manual)</div>
				<div class="card-body">
				<?php if($manual_delivery): ?>
						<table class="table table-sm" id="man_delivery">
						<thead>
						<tr>
							<th>delivery_date</th>
							<th>volume</th>
							<th>netto_price</th>
							<th>lotnr</th>
							<th>due_date</th>
							<th>supplier</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($manual_delivery as $d): ?>
						<tr>
							<td><?php echo $d['delivery_slip']['regdate']; ?></td>
							<td><?php echo $d['volume']; ?></td>
							<td><?php echo $d['in_price']; ?></td>
							<td><?php echo $d['lotnr']; ?></td>
							<td><?php echo $d['eol']; ?></td>
							<td><?php echo $d['supplier']; ?></td>
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
	$("#deliveries").DataTable({
		scrollY:        "30vh",
		deferRender:    true,
		scroller:       true,
		"order": [[ 0, "desc" ]]
	});
	$("#man_delivery").DataTable({
		scrollY:        "30vh",
		deferRender:    true,
		scroller:       true,
		"order": [[ 0, "desc" ]]
	});
	$("#admin").addClass('active');
});
</script>
  