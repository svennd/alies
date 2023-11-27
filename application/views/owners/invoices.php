<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url('/'); ?>"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> <small>(#<?php echo $owner['id']; ?>)</small> / <?php echo $this->lang->line('invoices'); ?>		
	</div>
	<div class="card-body">
	<?php if($bills): ?>
	<table class="table" id="dataTable">
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
		  <td><?php echo ($bill['card'] != 0) ? $bill['card'] . "&euro;" : ""; ?></td>
		  <td><?php echo ($bill['cash'] != 0) ? $bill['cash'] . "&euro;" : ""; ?></td>
		  <td><?php echo ($bill['transfer'] != 0) ? $bill['transfer'] . "&euro;" : ""; ?></td>
		  <td><?php echo $bill['vet']['first_name']; ?></td>
		  <td><?php echo $bill['location']['name']; ?></td>
		</tr>
		<?php endforeach; ?>
	  </tbody>
	</table>
	<?php else: ?>
		<?php echo $this->lang->line('no_invoices'); ?>
	<?php endif; ?>
	</div>
</div>



<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
			"order": [[ 1, "desc" ]],
		responsive: {
        	"details": {
            "type": 'column',
            "target": 'tr'
        }
  	  },
	});
	$("#home").addClass('active');
});
</script>