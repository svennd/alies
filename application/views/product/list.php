<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><a href="<?php echo base_url(); ?>products"><?php echo $this->lang->line('products'); ?></a> / <?php echo $this->lang->line('list_products') ?></div>
		<?php if ($this->ion_auth->in_group("admin")): ?>
		<div class="dropdown no-arrow">
			<a href="<?php echo base_url(); ?>products/new" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i> new product</a>
		</div>
		<?php endif; ?>
	</div>

	<div class="card-body">
	<div class="mb-3">
		<?php foreach ($root_types as $type): ?>
			<a href="<?php echo base_url('products/product_list/' . $type['id']); ?>" class="btn btn-sm <?php echo ($type['id'] == $query || $type['id'] == $selected_root) ? 'btn-primary' : 'btn-outline-primary'; ?>" ><?php if (!empty($type['icon'])): ?><i class="<?php echo html_escape($type['icon']); ?> mr-1" <?php echo !empty($type['icon_color']) ? 'style="color: ' . html_escape($type['icon_color']) . ';"' : ''; ?>></i><?php endif; ?><?php echo html_escape($type['name']); ?></a>
		<?php endforeach; ?>
		<?php foreach ($special_types as $type): ?>
			<a href="<?php echo base_url('products/product_list/' . $type['id']); ?>" class="btn btn-sm <?php echo ($type['id'] == $query) ? 'btn-primary' : 'btn-outline-primary'; ?>" ><?php echo $type['name']; ?></a>
		<?php endforeach; ?>
	</div>
	<?php if (!is_null($selected_root) && !empty($child_types[$selected_root])): ?>
		<div class="mb-3">
			<?php foreach ($child_types[$selected_root] as $type): ?>
				<a href="<?php echo base_url('products/product_list/' . $type['id']); ?>" class="btn btn-sm <?php echo ($type['id'] == $query) ? 'btn-success' : 'btn-outline-success'; ?>" ><?php if (!empty($type['icon'])): ?><i class="<?php echo html_escape($type['icon']); ?> mr-1" <?php echo !empty($type['icon_color']) ? 'style="color: ' . html_escape($type['icon_color']) . ';"' : ''; ?>></i><?php endif; ?><?php echo html_escape($type['name']); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<hr/>
		<table class="table table-sm" id="dataTable">
		<thead>
		<tr>
			<th><?php echo $this->lang->line("name"); ?></th>
			<th><?php echo $this->lang->line('local_stock'); ?> / <?php echo $this->lang->line('global_stock'); ?></th>
			<th>details</th>
			<th>labels</th>
			<th><?php echo $this->lang->line("actions"); ?></th>
		</tr>
		</thead>
		</table>
		</div>
</div>

<script type="text/javascript">
const URL_LIST_PROD 	= '<?php echo base_url('products/get/'. $query); ?>';
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
			ajax:           URL_LIST_PROD,
			scrollY:        680,
			deferRender:    true,
			scroller:       true
	});
	$("#products").addClass('active');
});
</script>
