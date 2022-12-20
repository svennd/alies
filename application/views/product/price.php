<style type="text/css">
.input5 { width: 150px !important; }
</style>

<div class="row">

      <div class="col-lg-8 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> /
				<a href="<?php echo base_url(); ?>products/product_price">Price List</a> /
				<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a> / 
                <?php echo $this->lang->line('price_setting'); ?>
			</div>
            <div class="card-body">
			<?php if ($product): ?>

				<?php if (!is_null($product['prices'])): ?>
					<table class="table">
						<tr>
							<th><?php echo $this->lang->line('price_from'); ?></th>
							<th><?php echo $this->lang->line('price_sale'); ?></th>
							<th><?php echo $this->lang->line('margin'); ?></th>
							<th><?php echo $this->lang->line('options'); ?></th>
						</tr>
				<?php 
					
					$unit_price = ($product['buy_price']/$product['buy_volume']);

					foreach($product['prices'] as $price):
						
					$change = round((($unit_price-$price['price'])/$unit_price)*100*-1);
				?>
				<tr>
					<td class="input5">
						<form method="post" id="form<?php echo $price['id'] ?>" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>">
						<div class="input-group ">
							<input type="text" class="form-control" id="volume" name="volume" placeholder="" value="<?php echo $price['volume']; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
						<input type="hidden" name="price_id" value="<?php echo $price['id']; ?>" />
						</form>
					</td>
					<td class="input5">
						<div class="input-group input5">
							<input type="text" class="form-control" id="price" name="price" placeholder="" value="<?php echo $price['price']; ?>" form="form<?php echo $price['id'] ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</td>
					<td class="input5">
						<input class="form-control <?php echo ($change > 0) ? 'is-valid' : 'is-invalid' ?>" type="text" placeholder="<?php echo $change; ?>%" readonly>
					</td>
					<td>
						<button type="submit" name="submit" value="edit" class="btn btn-primary btn-sm" form="form<?php echo $price['id'] ?>"><i class="fas fa-save"></i> <?php echo $this->lang->line('store'); ?></button>
						<a href="<?php echo base_url(); ?>products/remove_product_price/<?php echo $price['id']; ?>" class="btn btn-danger my-1 btn-sm"><i class="fas fa-trash-alt"></i> <?php echo $this->lang->line('remove'); ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
					<tr>
					<td class="input5">
						<form method="post" id="form_new" action="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>">
						<div class="input-group">
							<input type="text" class="form-control" id="volume" name="volume" placeholder="">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
						</form>
					</td>
					<td class="input5">
					<div class="input-group">
						<input type="text" class="form-control" id="price" name="price" placeholder="" form="form_new">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
					</td>
					<td class="input5">&nbsp;</td>
					<td>
						<button type="submit" name="submit" form="form_new" value="store" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add'); ?></button>
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
			<div class="card-header"><?php echo $this->lang->line('ref_price'); ?></div>
            <div class="card-body">
				<table class="table table-sm">
				<?php if ($product['buy_volume'] != 1): ?>
					<tr>
						<th>&nbsp;</td>
						<th><?php echo $product['buy_volume']. " " . $product['unit_buy']; ?></td>
						<th>1 <?php echo $product['unit_buy']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? $product['wholesale']['bruto'] : '---'; ?> &euro;</td>
						<td><?php echo (isset($product['wholesale'])) ? round($product['wholesale']['bruto']/$product['buy_volume'] , 2) : '---'; ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_practice'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? round(($product['wholesale']['bruto'])*0.91, 2) : '---'; ?> &euro;</td>
						<td><?php echo (isset($product['wholesale'])) ? round(($product['wholesale']['bruto']*0.91)/$product['buy_volume'] , 2) : '---'; ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_alies'); ?></td>
						<td><?php echo $product['buy_price']; ?> &euro;</td>
						<td><?php echo round($product['buy_price']/$product['buy_volume'] , 2); ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale_sell'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? $product['wholesale']['sell_price'] : '---'; ?> &euro;</td>
						<td><?php echo (isset($product['wholesale'])) ? round($product['wholesale']['sell_price']/$product['buy_volume'] , 2) : '---'; ?> &euro;</td>
					</tr>
				<?php else: ?>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? $product['wholesale']['bruto'] : '---'; ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_practice'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? round(($product['wholesale']['bruto'])*0.91, 2) : '---'; ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_alies'); ?></td>
						<td><?php echo $product['buy_price']; ?> &euro;</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('price_wholesale_sell'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? $product['wholesale']['sell_price'] : '---'; ?> &euro;</td>
					</tr>
				<?php endif; ?>
				</table>

                
				<?php if($stock_price): ?>
                <i><?php echo $this->lang->line('day_prices'); ?> :</i>
				<table class="table table-sm">
					<tr>
						<th><?php echo $this->lang->line('price_dayprice'); ?></td>
						<th><?php echo $product['buy_volume']. " " . $product['unit_buy']; ?></td>
						<th><?php echo "1 " . $product['unit_buy']; ?></td>
					</tr>
					<?php foreach($stock_price as $stock): ?>
					<tr>
						<td><?php echo user_format_date($stock['created_at'], $user->user_date); ?></td>
						<td><?php echo $stock['in_price']; ?> &euro; </td>
						<td><?php echo round($stock['in_price']/$product['buy_volume'] , 2) ; ?> &euro; </td>
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
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#prod_list").addClass('active');
	
});
</script>
