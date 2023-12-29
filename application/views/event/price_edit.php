<?php 
// local helper
function show_difference($price, $ori_price)
{
	if ($ori_price != 0)
	{
		$change = round((($price-$ori_price)/$ori_price)*100);
		if ($change == 0)
			return "&nbsp;";
		
		return ($change < 0) ? 
						'<span style="color:red;">' . $change . '%</span>' 
					: 
						'<span style="color:green;">' . $change . '%</span>';
	}
	else
	{
		return "&nbsp;";
	}
}

?>
<style>
    .crossed-out {
      position: relative;
      display: inline-block;
    }

    .crossed-out::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      border-top: 1px solid rgba(0, 0, 0, 0.31); /* Change color as needed */
      transform: translateY(-50%) rotate(-45deg);
      transform-origin: center center;
    }
</style>
<div class="row">
	<div class="col-lg-7 col-xl-10">
	<div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?><?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
			<a href="<?php echo base_url('pets/fiche/'. $pet['id']); ?>"><?php echo $pet['name'] ?></a> / 
			<a href="<?php echo base_url('events/event/' . $event_info['id']); ?>">Event</a> /
			<i class="fas fa-skull-crossbones"></i> <?php echo $this->lang->line('edit_price'); ?>
		</div>
		<div class="card-body">
			<?php
				$total = 0;
				$total_ex = 0;
			?>
			<table class="table">
			<thead>
				<tr class="thead-light">
					<th><?php echo $this->lang->line('name'); ?></th>
					<th><?php echo $this->lang->line('price_per_unit'); ?></th>
					<th><?php echo $this->lang->line('original_price'); ?></th>
					<th><?php echo $this->lang->line('new_price_ex_vat'); ?></th>
					<th><?php echo $this->lang->line('change_price'); ?></th>
					<th><?php echo $this->lang->line('inc_vat'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php include "price/block_procedures_edit.php"; ?>
			<?php include "price/block_consumables_edit.php"; ?>
			
			</tbody>
			<tfoot>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><i><?php echo $this->lang->line('ex_vat'); ?><br/><?php echo $this->lang->line('inc_vat'); ?></i></td>
					<td><i><?php echo round($total_ex, 2); ?><br/><?php echo round($total, 2); ?></i></td>
				</tr>
			</tfoot>
			</table>
			<a href="<?php echo base_url('events/event/' . $event_info['id']); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> <?php echo $this->lang->line('return_to_event'); ?></a>
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

	$('.send').click(function(e) {
		e.preventDefault();
		var linkData = parseFloat($(this).data('float-value'));
		var id = $(this).data('id');

		$('#volume' + id).val(linkData);
		$('#reason' + id).val("TIER_REDUCTION");
		
		$(`#form${id} [name="submit"]`).click();
	});
});
</script>
