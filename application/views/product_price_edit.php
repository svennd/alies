<div class="row">

      <div class="col-lg-8 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> /
				<a href="<?php echo base_url(); ?>products/product_price">Price List</a> /
				<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a> / pricing
			</div>
            <div class="card-body">
			<?php if ($product): ?>
			<h4>Current Sell Price</h4>
				<?php if (!is_null($product['prices'])): ?>
					<table class="table">
						<tr>
							<th>From</th>
							<th>Price</th>
							<th>Margin</th>
							<th>Options</th>
						</tr>
				<?php foreach($product['prices'] as $price):
					$unit_price = ($product['buy_price']/$product['buy_volume']);
					$change = round((($unit_price-$price['price'])/$unit_price)*100*-1);
				?>
				<tr>
					<td>
						<form method="post" id="form<?php echo $price['id'] ?>" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>">					
						<div class="input-group">
							<input type="text" class="form-control" id="volume" name="volume" placeholder="" value="<?php echo $price['volume']; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
						<input type="hidden" name="price_id" value="<?php echo $price['id']; ?>" />
						</form>
					</td>
					<td>
						<div class="input-group">
							<input type="text" class="form-control" id="price" name="price" placeholder="" value="<?php echo $price['price']; ?>" form="form<?php echo $price['id'] ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</td>
					<td>
						<input class="form-control <?php echo ($change > 0) ? 'is-valid' : 'is-invalid' ?>" type="text" placeholder="<?php echo $change; ?>%" readonly>
					</td>
					<td>
						<button type="submit" name="submit" value="edit" class="btn btn-primary btn-sm" form="form<?php echo $price['id'] ?>">Store</button>
						<a href="<?php echo base_url(); ?>products/remove_product_price/<?php echo $price['id']; ?>" class="btn btn-danger my-1 btn-sm">remove</a>
					</td>
				</tr>
				<?php endforeach; ?>
					</table>

					<hr />
					<h4>Add price</h4>
					<p>Add volume reduction;</p>
					<table class="table">
						<tr>
							<th>From</th>
							<th>Price</th>
							<th>&nbsp;</th>
							<th>Options</th>
						</tr>
					<tr>
					<td>
						<form method="post" id="form_new" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>">
						<div class="input-group">
							<input type="text" class="form-control" id="volume" name="volume" placeholder="">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
						</form>
					</td>
					<td>
					<div class="input-group">
						<input type="text" class="form-control" id="price" name="price" placeholder="" form="form_new">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
					</td>
					<td>&nbsp;</td>
					<td>
						<button type="submit" name="submit" form="form_new" value="store" class="btn btn-primary btn-sm">Store</button>
					</td>
					</tr>
					</table>
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
	<div class="col-lg-4 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> /
				<a href="<?php echo base_url(); ?>products/product_price">Price List</a> /
				<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a> / info
			</div>
            <div class="card-body">
				<table class="table">
					<tr>
						<td>Catalog Price</td>
						<td><?php echo $product['buy_price']; ?> &euro;</td>
					</tr>
				</table>
				<h4>Current stock :</h4>
				<?php if($stock_price): ?>
				<table class="table">
					<tr>
						<th>buy price</td>
						<th>Volume</td>
						<th>Date</td>
					</tr>
					<?php foreach($stock_price as $stock): ?>
					<tr>
						<td><?php echo $stock['in_price']; ?> &euro; </td>
						<td><?php echo $stock['volume']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><?php echo user_format_date($stock['created_at'], $user->user_date); ?></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php else: ?>
					no stock found.
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
