<div class="row">
	<div class="col-lg-7 col-xl-10">
	<div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
			<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo $pet['name'] ?></a> / 
			<a href="<?php echo base_url(); ?>events/event/<?php echo $event_info['id']; ?>">Event</a> /
			<i class="fas fa-skull-crossbones"></i> Edit prices
		</div>
		<div class="card-body">
			<?php
				$total = 0;
				$total_ex = 0;
			?>
			<table class="table">
			<thead>
				<tr class="thead-light">
					<th>Name</th>
					<th>Price Per Unit</th>
					<th>Original Price</th>
					<th>New Price</th>
					<th>Change</th>
					<th>Inc. BTW</th>
				</tr>
			</thead>
			<tbody>
			<?php include "event/block_procedures_edit.php"; ?>
			<?php include "event/block_consumables_edit.php"; ?>
			
			</tbody>
			<tfoot>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><i>ex. VAT<br/>in. VAT</i></td>
					<td><i><?php echo round($total_ex, 2); ?><br/><?php echo round($total, 2); ?></i></td>
				</tr>
			</tfoot>
			</table>
		</div>
	</div>
	</div>
	<div class="col-lg-5 col-xl-2">
		<?php include "event/block_client.php"; ?>
		
		<div class="card border-left-success shadow py-1 mb-3">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-uppercase mb-1">Auto Reduction</div>
						<div class="btn-group" role="group" aria-label="Button group with links">
							<a href="<?php echo base_url(); ?>events/edit_event_price/<?php echo $event_info['id']; ?>/0" class="btn btn-primary btn-sm"><i class="fa-solid fa-rotate-left"></i></a>
							<a href="<?php echo base_url(); ?>events/edit_event_price/<?php echo $event_info['id']; ?>/5" class="btn btn-info btn-sm">5%</a>
							<a href="<?php echo base_url(); ?>events/edit_event_price/<?php echo $event_info['id']; ?>/10" class="btn btn-info btn-sm">10%</a>
							<a href="<?php echo base_url(); ?>events/edit_event_price/<?php echo $event_info['id']; ?>/15" class="btn btn-info btn-sm">15%</a>
						</div>
					</div>
					<div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$(document).ready(function() {
    $('.changeprice').click(function() {
      const change = $(this).data('change');
      const form = $(this).data('form');
      const price = $(this).data('price');
      calculate_change_and_push(change, form, price);
    });
  });

  function calculate_change_and_push(change, form, price) {
		let new_price = price - (price * (change/100));
		console.log(new_price);
		$(`#${form} [name="price"]`).val(new_price.toFixed(2));
 		$(`#${form} [name="submit"]`).click();
	}
});
</script>
