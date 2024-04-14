<?php 
function is_full_value($value, float $total): string
{
	if ($value == "" || $value == 0) 
	{
		return '';
	}
	
	return ($value == $total) ? '<i class="fa-regular fa-fw fa-circle-check"></i>' : $value . "&euro;";
}
?>
<div class="card shadow mb-4">
	 	<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url('/'); ?>"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> <small>(#<?php echo $owner['id']; ?>)</small> / <?php echo $this->lang->line('invoices'); ?></div>
	</div>
	<div class="card-body">
	<form action="<?php echo base_url('owners/invoices/'. $owner['id']); ?>" method="post" autocomplete="off" class="form-inline">
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

	<?php if($bills): ?>
	<table class="table table-sm" id="dataTable">
	  <thead>
		<tr>
		  <th><?php echo $this->lang->line('Invoice'); ?></th>
		  <th data-priority="1"><?php echo $this->lang->line('date'); ?></th>
		  <th><?php echo $this->lang->line('state'); ?></th>
		  <th data-priority="2"><?php echo $this->lang->line('amount'); ?></th>
		  <th><?php echo $this->lang->line('card'); ?></th>
		  <th><?php echo $this->lang->line('cash'); ?></th>
		  <th><?php echo $this->lang->line('transfer'); ?></th>
		  <th data-priority="3"><?php echo $this->lang->line('vet'); ?></th>
		  <th data-priority="4"><?php echo $this->lang->line('location'); ?></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach($bills as $bill): ?>
		<tr>
		  <td>
			<a href="<?php echo base_url('invoice/get_bill/' . $bill['id']); ?>">
				<?php if($bill['invoice_id']): ?>
					#<?php echo get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']); ?>
				<?php else: ?>
					#<?php echo get_bill_id($bill['id']); ?>
				<?php endif; ?>
			</a>
		</td>
		  <td data-sort="<?php echo strtotime($bill['created_at']) ?>"><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
		  <td><?php echo get_bill_status($bill['status'], true); ?></td>
		  <td data-sort="<?php echo $bill['total_brut'];?>"><?php echo $bill['total_brut']; ?> &euro;</td>
		  <td><?php echo is_full_value($bill['card'], $bill['total_brut']); ?></td>
		  <td><?php echo is_full_value($bill['cash'], $bill['total_brut']); ?></td>
		  <td><?php echo is_full_value($bill['transfer'], $bill['total_brut']); ?></td>
		  <td><?php echo $bill['vet']['first_name']; ?></td>
		  <td><?php echo ($bill['location']) ? $bill['location']['name'] : ''; ?></td>
		</tr>
		<?php endforeach; ?>
	  </tbody>
	</table>
	<?php endif; ?>
	</div>
</div>



<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
		order: [[ 1, "desc" ]],
		responsive: {
        	"details": {
            "type": 'column',
            "target": 'tr'
        }
  	  },
	lengthChange: false
	});
	$("#home").addClass('active');
});
</script>