<div class="row">
      <div class="col-lg-8 mb-4">

		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> / Search Product
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>products" method="post" autocomplete="off" class="form-inline">
					<label class="sr-only" for="name">product name</label>
					<input type="text" class="form-control mb-2 mr-sm-2" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($search_q)) ? $search_q : 'Product Name'; ?>">
					<button type="submit" name="submit" value="search_product" class="btn btn-primary mb-2">Search</button>
				</form>
			<?php if ($search): ?>
			<ul>
				<?php foreach($search as $sear): ?>
					<li><a href="<?php echo base_url(); ?>products/product/<?php echo $sear['id']; ?>"><?php echo $sear['name']; ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			</div>
		</div>

		<div class="card shadow mb-4">
			<div class="card-header">Products</div>
            <div class="card-body">
				By category :
				<?php if ($product_types) : ?>
				<ul>
				<?php foreach($product_types as $type): ?>
					<li><a href="<?php echo base_url(); ?>products/product_list/<?php echo $type['id']; ?>"><?php echo $type['name']; ?></a> <?php echo (isset($type['products'])) ? '( ' . $type['products'][0]['counted_rows'] . ' )' : '';?></li>
				<?php endforeach; ?>
					<li><a href="<?php echo base_url(); ?>products/product_list">List All Products</a></li>
				</ul>
				<?php else: ?>
					No categories defined.
				<?php endif; ?>
				<?php if ($this->ion_auth->in_group("admin")): ?>
				Specific Queries :
				<ul>
					<li><a href="<?php echo base_url(); ?>products/product_price">Price Only List</a></li>
				</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
      <div class="col-lg-4 mb-4">

	  <?php if ($this->ion_auth->in_group("admin")): ?>
		<a href="<?php echo base_url(); ?>products/new" class="btn btn-success btn-lg mb-3"><i class="fas fa-cart-plus"></i> New Product</a>
		<?php endif; ?>
	  <div class="card shadow mb-4">
			<div class="card-header">Last Modified Products</div>
            <div class="card-body">
				<?php if ($last_modified) : ?>
				<ul>
					<?php foreach($last_modified as $mod): ?>
					<li><a href="<?php echo base_url(); ?>products/product/<?php echo $mod['id']; ?>"><?php echo $mod['name']; ?></a> <small>(<?php echo time_ago($mod['updated_at']); ?>)</small></li>
					<?php endforeach; ?>
				</ul>
				<?php else: ?>
					No Updates.
				<?php endif; ?>
			</div>
		</div>

      <div class="card shadow mb-4">
			<div class="card-header">Last Created Products</div>
            <div class="card-body">
				<?php if ($last_created) : ?>
				<ul>
					<?php foreach($last_created as $mod): ?>
					<li><a href="<?php echo base_url(); ?>products/product/<?php echo $mod['id']; ?>"><?php echo $mod['name']; ?></a> <small>(<?php echo time_ago($mod['created_at']); ?>)</small></li>
					<?php endforeach; ?>
				</ul>
				<?php else: ?>
					No created.
				<?php endif; ?>
			</div>
		</div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
});
</script>
