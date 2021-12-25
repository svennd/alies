<div class="row"> 
	<div class="col-lg-10 mb-4">


		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>reports">Reports</a> / <a href="<?php echo base_url(); ?>reports/products">Product</a> / range usage
			</div>
			<div class="card-body">
				<?php if($results): ?>
					<table class="table">
					<tr>
						<td>Product_name</td>
						<td>Volume</td>
						<td>net_price</td>
						<td>vet_name</td>
						<td>stck.name</td>
						<td>updated_at</td>
					</tr>
					<?php foreach($results as $line): ?>
					<tr>
						<td><?php echo $line['product_name']; ?></td>
						<td><?php echo $line['volume'] . ' ' . $line['unit_sell']; ?></td>
						<td><?php echo $line['net_price']; ?></td>
						<td><?php echo $line['vet_name']; ?></td>
						<td><?php echo $line['name']; ?></td>
						<td><a href="<?php echo base_url(); ?>events/event/<?php echo $line['event_id'] ?>"><?php echo date_format(date_create($line['updated_at']), $user->user_date); ?></a></td>
					</tr>
					<?php endforeach; ?>
					</table>
				<?php endif; ?>
			</div>
		</div>		
	</div>
</div>