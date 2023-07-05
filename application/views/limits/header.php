<div class="card-header border-bottom">
	<ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
		<li class="nav-item" role="presentation"><a class="nav-link <?php echo (!isset($filter) && isset($order)) ? "active":'';?>" href="<?php echo base_url('limits/order'); ?>"><?php echo $this->lang->line('backorder'); ?></a></li>
		<li class="nav-item" role="presentation"><a class="nav-link <?php echo (!isset($filter) && !isset($order)) ? "active":'';?>" href="<?php echo base_url('limits/global'); ?>" role="tab"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('global'); ?>)</a></li>
		<?php foreach ($locations as $loc): ?>
			<li class="nav-item" role="presentation"><a class="nav-link <?php echo (isset($filter) && $loc['id'] == $filter) ? 'active' : '';?>" href="<?php echo base_url('limits/local/' . $loc['id']); ?>"><?php echo $loc['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
