<?php
$state = array(
				"OPEN",
				"UNPAID",
				"NOT COMPLETE",
				"PAID",
				"NON_COLLECTABLE",
				);
?>

<div class="row">
	<div class="col-lg-12 mb-4">
	<div class="card mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Invoice</h6>
		</div>
		<div class="card-body">
			<div class="alert alert-danger" role="alert">
			  Not (longer) a valid bill !
			</div>
			<table class="table">
			<thead>
				<tr>
					<td>Total_amount</td>
					<td><?php echo $bill['amount']; ?> &euro;</td>
				</tr>
				<tr>
					<td>cash</td>
					<td><?php echo $bill['cash']; ?> &euro;</td>
				</tr>
				<tr>
					<td>card</td>
					<td><?php echo $bill['card']; ?> &euro;</td>
				</tr>
				<tr>
					<td>created_at</td>
					<td><?php echo $bill['created_at']; ?></td>
				</tr>
				<tr>
					<td>state</td>
					<td><?php echo $state[$bill['status']]; ?></td>
				</tr>
			</thead>
			</table>
		</div>
	</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#invoice").addClass('active');
});
</script>