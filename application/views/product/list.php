	<p>
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
			<?php foreach ($types as $type): ?>
				<a href="<?php echo base_url('products/product_list/' . $type['id']); ?>" class="btn btn-sm btn-outline-primary"><?php echo $type['name']; ?></a>
			<?php endforeach; ?>
			<hr/>
				<table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line("name"); ?></th>
					<th><?php echo $this->lang->line("alternative_name"); ?></th>
					<th><?php echo $this->lang->line("type"); ?></th>
				</tr>
				</thead>
				</table>
                </div>
		</div>
	</p>
	
<script type="text/javascript">
const URL_LIST_PROD 	= '<?php echo base_url('products/get/'. $query); ?>';
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
			ajax:           URL_LIST_PROD,
			scrollY:        580,
			deferRender:    true,
			scroller:       true
	});
	$("#products").addClass('active');
});
</script>
