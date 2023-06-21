<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('products'); ?>">Stock</a> / <?php echo $this->lang->line('expired_stock'); ?>
			</div>
      <div class="card-body">
			<p><?php echo $this->lang->line('expired_stock_expl'); ?>
        <span class="p-1 bg-danger text-white">Expiring soon (30d)</span>, <span class="p-1 bg-secondary text-white">Expired</span>.<br/></p>
			<?php if ($stock_gone_bad): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('product'); ?></th>
					<th><?php echo $this->lang->line('eol'); ?></th>
					<th><?php echo $this->lang->line('lotnr'); ?></th>
					<th><?php echo $this->lang->line('volume'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('barcode'); ?></th>
					<th><?php echo $this->lang->line('option'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($stock_gone_bad as $expire):
				?>
				<tr>
					<td><?php echo $expire['products']['name']; ?></td>
					<td data-sort="<?php echo strtotime($expire['eol']); ?>"><?php echo date_format(date_create($expire['eol']), $user->user_date); ?></td>
					<td><?php echo $expire['lotnr']; ?></td>
					<td><?php echo $expire['volume']; ?> <?php echo $expire['products']['unit_buy']; ?></td>
					<td><?php echo $expire['stock_locations']['name']; ?></td>
					<td><?php echo $expire['barcode']; ?></td>
          <td>
            <?php
              $date = new DateTime($expire['eol']);
              $now = new DateTime();

              if($date < $now):
            ?>
            <form action="<?php echo base_url(); ?>stock/write_off" method="post" autocomplete="off">
    					<input type="hidden" name="volume" value="<?php echo $expire['volume']; ?>">
    					<input type="hidden" name="product_id" value="<?php echo $expire['products']['id']; ?>">
    					<input type="hidden" name="location" value="<?php echo $expire['stock_locations']['id']; ?>">
    					<input type="hidden" name="barcode" value="<?php echo $expire['barcode']; ?>">
    				  <button type="submit" name="submit" value="write_off_q" class="btn btn-primary">Write off</button>
    				</form>
            <?php endif; ?>
          </td>
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

	$("#product_list").addClass('active');

	const current_date = new Date();

	$("#dataTable").DataTable({
			"order": [[ 1, "asc" ]],
			"createdRow": function( row, data, dataIndex){
				var product_date = new Date(data[1]["@data-sort"]*1000);
				date_diff = (current_date - product_date);
				if( date_diff > 0){
					$(row).addClass("table-secondary");
				/* 30 days */
				} else if (date_diff > -(1000*60*60*24*30)) {
					$(row).addClass("table-danger");
				}
			}
	});
});
</script>
