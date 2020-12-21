<div class="card shadow mb-4">
	<div class="card-header py-3">
	  <h6 class="m-0 font-weight-bold text-primary">Stock Game</h6>
	</div>
	<div class="card-body">
		<div class="media">
		  <img src="<?php echo base_url(); ?>assets/fifi_q.png" class="align-self-start mr-3">
		  <div class="media-body">
			<h5 class="mt-0">What volume is in <i><?php echo $current_location; ?></i> for this lotnr of <?php echo $result['products']['name']; ?>?</h5>
				<p>
					<table class="table">
					<tr>
						<td>Name</td>
						<td>
							<?php echo $result['products']['name']; ?><br/>
							<small><i><?php echo $result['products']['short_name']; ?></i></small>
						</td>
					</tr>
					<tr>
						<td>Barcode</td>
						<td>
							<?php echo $result['barcode']; ?><br/>
						</td>
					</tr>
					<tr>
						<td>EOL</td>
						<td>
							<?php echo $result['eol']; ?><br/>
						</td>
					</tr>
					<tr>
						<td>lotnr</td>
						<td>
							<?php echo $result['lotnr']; ?><br/>
						</td>
					</tr>
					</table>
				</p>
			</div>
		</div>
		<div class="row">
			<?php 
			function randomize($input)
			{
				$a = rand(-4, +4);
				$a = ($a == 0) ? rand(1,3) : $a; # otherwise we might get 3 times the same
				$b = rand(0,1);
				if ($b) {
					$result = $input - $a;
				}
				else
				{
					$result = $input + $a;
				}
				return ($result < 0) ? abs($result) : $result;
			}
			
			$table = array((float)$result['volume'], randomize($result['volume']), randomize($result['volume']));
			shuffle($table);
			foreach($table as $t):
			
			?>
			<div class="col-sm-3">
				<div class="card text-center">
					<div class="card-body">
						<h5 class="card-title"><?php echo $t . ' ' . $result['products']['unit_sell']; ?></h5>
						
						<form action="<?php echo base_url(); ?>games/stock" method="post" autocomplete="off">
						<input type="hidden" name="value" value="<?php echo $t; ?>" />
						<button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
						</form>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="col-sm-3">
				<div class="card text-center">
					<div class="card-body">
						<h5 class="card-title">
						<form action="<?php echo base_url(); ?>games/stock" method="post" autocomplete="off">
						  <div class="form-group">
							<input type="text" name="volume" class="form-control form-control-sm" id="volume" placeholder="other value">
						  </div>
						</form>
						</h5>
						<button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#games").addClass('active');
});
</script>