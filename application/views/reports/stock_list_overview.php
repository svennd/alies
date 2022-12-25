<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
		  <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div>
                    <a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('stock_list'); ?>
                </div>
            </div>
            <div class="card-body">
			<?php echo $this->lang->line('stock_list_explain'); ?>
			<br/>
			<?php if ($locations): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('export'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc): ?>
				<tr>
					<td><?php echo $loc['name']; ?></td>
					<td><a href="<?php echo base_url(); ?>reports/stock_list/<?php echo $loc['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv"></i> <?php echo $this->lang->line('export'); ?></a></td>
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
	$("#admin").addClass('active');
});
</script>
  