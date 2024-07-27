<div class="card shadow mb-4">
	 	<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url('/'); ?>"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> <small>(#<?php echo $owner['id']; ?>)</small> / <?php echo $this->lang->line('products'); ?></div>
	</div>
	<div class="card-body">
	<form action="<?php echo base_url('owners/products/'. $owner['id']); ?>" method="post" autocomplete="off" class="form-inline">
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

	<?php if($products): ?>
	<table class="table table-sm" id="dataTable">
	  <thead>
		<tr>
		  <th><?php echo $this->lang->line('date'); ?></th>
		  <th><?php echo $this->lang->line('product'); ?></th>
		  <th><?php echo $this->lang->line('volume'); ?></th>
		  <th><?php echo $this->lang->line('pet_info'); ?></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach($products as $prod): ?>
		<tr>
		  <td data-sort="<?php echo strtotime($prod['event_date']) ?>"><?php echo user_format_date($prod['event_date'], $user->user_date); ?></td>
		  <td><a href="<?php echo base_url('products/profile/' . $prod['product_id']); ?>"><?php echo $prod['product_name']; ?></a></td>
		  <td><?php echo $prod['volume'] . ' ' . $prod['unit_sell']; ?></td>
		  <td><a href="<?php echo base_url('pets/fiche/' . $prod['pet_id']); ?>"><?php echo $prod['pet_name']; ?></a></td>
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
		responsive: {
        	"details": {
            "type": 'column',
            "target": 'tr'
        }
  	  },
	lengthChange: false,
	order: [[ 0, "desc" ]]
	});
	$("#home").addClass('active');
});
</script>