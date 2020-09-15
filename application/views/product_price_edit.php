<div class="row">
      <div class="col-lg-12 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> / <a href="<?php echo base_url(); ?>products/product_price">List</a> / edit price
			</div>
            <div class="card-body">
			<br/>
			<?php if ($product): ?>
				<h3><?php echo $product['name']; ?></h3>
				<?php if (!is_null($product['prices'])): ?>
				<?php foreach($product['prices'] as $price): ?>
					<form method="post" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" class="form-inline">
					
					<label class="sr-only" for="volume">volume</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
						<span class="input-group-text" id="basic-addon1">from</span>
						</div>
						<input type="text" class="form-control" id="volume" name="volume" placeholder="" value="<?php echo $price['volume']; ?>">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
					<p class="mb-2">
					drops to  &nbsp;
					</p>
					<label class="sr-only" for="price">price</label>
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" id="price" name="price" placeholder="" value="<?php echo $price['price']; ?>">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
						<input type="hidden" name="price_id" value="<?php echo $price['id']; ?>" />
						<button type="submit" name="submit" value="store" class="btn btn-primary mb-2">Store</button>
						<a href="<?php echo base_url(); ?>products/remove_product_price/<?php echo $price['id']; ?>" class="btn btn-danger mx-3 mb-2">remove</a>
					</form>
				<?php endforeach; ?>
					<hr />
					<h4>Add price</h4>
					<p>Add volume reduction;</p>
					
					<form method="post" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" class="form-inline">

					<label class="sr-only" for="volume">volume</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
						<span class="input-group-text" id="basic-addon1">from</span>
						</div>
						<input type="text" class="form-control" id="volume" name="volume" placeholder="">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
					<p class="mb-2">
					drops to  &nbsp;
					</p>
					<label class="sr-only" for="price">price</label>
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" id="price" name="price" placeholder="">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
						<button type="submit" name="submit" value="store" class="btn btn-primary mb-2">Store</button>
					</form>
				<?php else: ?>
					No price assigned yet ! <br/>
					
					<form method="post" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" class="form-inline">

					<label class="sr-only" for="price">price</label>
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" id="price" name="price" placeholder="">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
						<input type="hidden" name="volume" value="1" />
						<button type="submit" name="submit" value="store" class="btn btn-primary mb-2">Store</button>
					</form>
				<?php endif; ?>
				<?php else : ?>
				<div class="alert alert-danger" role="alert">Product is not sellable, or can't have a price;</div>
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