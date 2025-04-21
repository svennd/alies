<style type="text/css">
.input5 { width: 150px !important; }
.input4 { width: 120px !important; }
</style>


<?php 
	$diff = ($product['wholesale']) ? (((($product['wholesale']['bruto'] / $product['wholesale']['last_bruto']) * 100) - 100)) : 0;
	$procent_diff = ($diff > 0) ? "<span class='text-danger'>+" . round($diff) . "%</span><br><small>(<span style='color:indianred;'>". $product['wholesale']['last_bruto'] . "</span> &euro;)</small>" : "";
	$date_diff = ($diff > 0) ? "<small><br/>" . $this->lang->line('since') . " ". user_format_date($product['wholesale']['last_bruto_date'], $user->user_date) . "</small>" : "";
?>

<div class="row">

      <div class="col-lg-7 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('pricing/prod'); ?>">Products</a> /
				<?php echo $product['name']; ?> / 
                <?php echo $this->lang->line('price_setting'); ?>
			</div>
            <div class="card-body">

			<form method="post" id="form" action="<?php echo base_url('pricing/prod/' . $product['id']); ?>">
			<?php if ($product): ?>

				<?php if (!is_null($product['prices'])): ?>
					<table class="table table-sm">
						<tr>
							<th><?php echo $this->lang->line('volume'); ?></th>
							<th><?php echo $this->lang->line('price_sale'); ?></th>
							<th><?php echo $this->lang->line('options'); ?></th>
						</tr>

				<?php foreach($product['prices'] as $price): ?>
				<?php // loop all prices per volume ?>
				<tr id="line_<?php echo $price['id'] ?>">
					<td class="input4">
						<div class="input-group input-group-sm">
							<input type="text" class="form-control" name="volume[]" value="<?php echo $price['volume']; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</td>
					<td class="input5">
						<div class="input-group input-group-sm">
							<input type="text" class="form-control" name="price[]" value="<?php echo $price['price']; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</td>
					<td>
						<a href="#" class="btn btn-danger btn-sm remove" data-price="<?php echo $price['id'] ?>"><i class="fas fa-trash-alt"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php // add new one ?>
				<tr>
					<td class="input4">
						<form method="post" id="form_new" action="<?php echo base_url('pricing/prod/' . $product['id']); ?>">
						<div class="input-group input-group-sm">
							<input type="text" class="form-control" id="volume" name="volume[]">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
						</form>
					</td>
					<td class="input5">
					<div class="input-group input-group-sm">
						<input type="text" class="form-control" name="price[]">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
					</td>
					<td>
						<button type="submit" name="submit" value="store" class="btn btn-success btn-sm"><i class="fas fa-plus"></i></button>
					</td>
				</tr>
				</table>
				<?php if ($diff > 0): ?>
					<div class="alert alert-info" style="width:450px;height:50px;" role="alert">
						<div class="form-group form-check">
							<input type="checkbox" class="form-check-input" id="accept_wholesale" name="accept_wholesale" value="1" checked>
							<input type="hidden" name="wholesale" value="<?php echo (int) $product['wholesale']['id'] ?>">
							<label class="form-check-label" for="accept_wholesale"><?php echo $this->lang->line('accept_wh_new_price'); ?></label>
						</div>
					</div>
				<?php endif; ?>
					<button type="submit" name="submit" value="store" class="btn btn-outline-success btn-sm"><?php echo $this->lang->line("store"); ?></button>
				</form>
				
				<?php else: ?>
					<?php // add initial ?>
					No price assigned yet ! <br/>

					<form method="post" action="<?php echo base_url('pricing/prod/' . $product['id']); ?>" class="form-inline">

					<label class="sr-only" for="price">price</label>
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" id="price" name="price[]" placeholder="">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
						<input type="hidden" name="volume[]" value="1" />
						<button type="submit" name="submit" value="store" class="btn btn-primary mb-2"><?php echo $this->lang->line("store"); ?></button>
					</form>
				<?php endif; ?>
				<?php else : ?>
				<div class="alert alert-danger" role="alert">Product is not sellable, or can't have a price;</div>
			<?php endif; ?>
			</div>

		</div>

		<div class="card shadow mb-4">
			<a href="#stock" class="d-block card-header py-3 collapsed" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="stock">
				<h6 class="m-0 font-weight-bold text-primary">Stock</h6>
			</a>
			<!-- <div class="card-header"></div> -->
			<div class="collapse" id="stock">
				<div class="card-body">
						<?php if($stock_price): ?>
							<table class="table table-sm">
								<tr>
									<th><?php echo $this->lang->line('eol'); ?></td>
									<th><?php echo $product['buy_volume']. " " . $product['unit_buy']; ?></td>
									<th><?php echo "1 " . $product['unit_buy']; ?></td>
								</tr>
							<?php foreach($stock_price as $stock): ?>
							<tr>
								<td><?php echo user_format_date($stock['eol'], $user->user_date); ?></td>
								<td><?php echo $stock['in_price']; ?> &euro; </td>
								<td><?php echo round($stock['in_price']/$product['buy_volume'] , 2) ; ?> &euro; </td>
							</tr>
							<?php endforeach; ?>
						</table>
						<?php else: ?>
							no stock found.
						<?php endif; ?> 
					</table>
				</div>
			</div>
		</div>
		
		<div class="card shadow mb-4">
			<a href="#history" class="d-block card-header py-3 collapsed" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="history">
				<h6 class="m-0 font-weight-bold text-primary">Price History</h6>
			</a>
			<!-- <div class="card-header"></div> -->
			<div class="collapse" id="history">
				<div class="card-body">
					<?php if($log_price): ?>
						<table class="table table-sm">
								<tr>
									<th width="100px;"><?php echo $this->lang->line('date'); ?></th>
									<th><?php echo $this->lang->line('volume'); ?></th>
								</tr>
							<tr>
						<?php foreach($log_price as $log_record): ?>
							<tr>
								<?php $log = json_decode($log_record['log'], true); ?>
								<?php $date = user_format_date($log_record['created_at'], $user->user_date); ?>
								<td><?php echo $date; ?></td>
								<td>
									<table class="table table-sm">
										<?php if ($log): ?>
										<tr>
											<?php foreach(array_keys($log) as $volume): ?>
											<td><?php echo $volume; ?></td>
											<?php endforeach; ?>
										</tr>
										<tr>
											<?php foreach(array_values($log) as $price): ?>
											<td><?php echo $price; ?></td>
											<?php endforeach; ?>
										</tr>
										<?php endif; ?>
									</table>
								</td>
							</tr>
						<?php endforeach; ?>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>

	</div>
	<div class="col-lg-5 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header"><?php echo $this->lang->line('ref_price'); ?></div>
            <div class="card-body">
				<table class="table table-sm">
					<tr>
						<th>&nbsp;</td>
						<th><?php echo $product['buy_volume']. " " . $product['unit_buy']; ?></td>
					</tr>
					<tr>
						<td><?php echo (isset($product['wholesale'])) ? "<a href='" . base_url('wholesale/get_history/' . $product['wholesale']['id']) . "'>" . $this->lang->line('catalog_price') . "</a>" . $date_diff: $this->lang->line('catalog_price'); ?></td>
						<td><?php echo (isset($product['wholesale'])) ? $product['wholesale']['bruto'] . " &euro; " . $procent_diff : '---'; ?></td>
					</tr>
					<tr>
						<td>
							<?php echo $this->lang->line('price_alies'); ?><br/>
							<small><?php echo user_format_date($product['buy_price_date'], $user->user_date); ?></small>
						</td>
						<td>
							<div id="manual_price">
								<?php echo $product['buy_price']; ?> &euro; <a href="#" class="btn btn-sm btn-outline-danger" id="manual_edit"><i class="fa-solid fa-wrench"></i></a>
							</div>
							<div id="manual_form" class="d-none">
								<form method="post" action="<?php echo base_url('pricing/man/' . $product['id']); ?>">
									<input type="number" min="0" max="1000" step="0.01" name="buy_price" class="form-control form-control-sm my-1" value="<?php echo $product['buy_price']; ?>" id="buy_price">
									<input type="date" name="buy_price_date" class="form-control form-control-sm my-1" id="buy_price_date" value="<?php echo date('Y-m-d') ?>">
									<button type="submit" name="submit" value="store" class="btn btn-sm btn-outline-primary mb-2">Store</button>
								</form>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>

	  </div>

</div>



<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#prod_list").addClass('active');
	
	$("#manual_edit").click(function(){
		$("#manual_price").addClass('d-none');
		$("#manual_form").removeClass('d-none');
	});

	$(".remove").click(function(){
		var price = $(this).data('price');
		$("#line_"+price).remove();
	});
});
</script>
