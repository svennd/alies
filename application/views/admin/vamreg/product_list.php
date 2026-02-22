<style>
	td.narrow-col {
  max-width: 15ch;
  white-space: normal;
  word-break: break-word;
}
</style>
<?php include 'blocks/menu.php'; ?>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / product list</div>
		<div>
	 		<a href="<?= base_url("vamreg/refresh") ?>" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-arrows-rotate"></i></a>
		</div>
    </div>
	<div class="card-body">
        <?php if($products): ?>

            <table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>CNK / CTI-e</th>
					<th>Product</th>
					<th>PackSize</th>
					<th>Product Name</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $buff): ?>
				<tr>
					<td>
						<?php echo $buff['cnk']; ?><br/>
						<small><i><?php echo $buff['cti']; ?></i></small>
					</td>
					<td>
						<?= strlen($buff['ppnNl']) > 80
							? substr($buff['ppnNl'], 0, 80).'<br>'.substr($buff['ppnNl'], 80)
							: $buff['ppnNl']; ?><br/>
						<small><i><?php echo $buff['maName']; ?> - <?php echo $buff['maNumber']; ?></i></small>
					</td>
					<td><?php echo $buff['packSize']; ?></td>
					<td>
						<a href="<?php echo site_url('products/product/' . $buff['product_id']); ?>"
						<?php if(!$buff['is_antibiotic']): ?>
							class="text-danger"
						<?php endif; ?>
						><?php echo $buff['product_name']; ?></a>
						<?php if($buff['product_id']): ?>
							<br/><small><i><?php echo $buff['ab_unit_volume'] . ' ' . $buff['ab_unit']; ?></i> (buy: <?php echo $buff['buy_volume'] . ' ' . $buff['unit_buy']; ?>, sell: <?php echo $buff['sell_volume'] . ' ' . $buff['unit_sell']; ?>)</small>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
        <?php endif;?>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#vamreg-product-list").addClass('active');

		var table = $('#dataTable').DataTable({
		dom: '<"d-flex justify-content-between mb-2"<"filter-buttons">f>t<"d-flex justify-content-between"ip>',
		lengthChange: false,
		initComplete: function () {
			$('.filter-buttons').html(`
				<button id="filter-empty" class="btn btn-sm btn-outline-danger mr-1"><i class="fa-solid fa-link-slash" style="color: rgba(230, 99, 99, 1.00);"></i> Unlinked</button>
				<button id="filter-nonempty" class="btn btn-sm btn-outline-success mr-1"><i class="fa-solid fa-link" style="color: rgba(99, 230, 190, 1);"></i> Linked</button>		
			`);
			// default to nonempty
			var api = this.api();
			var lastCol = api.columns().count() - 1;
			api.column(lastCol).search('.+', true, false).draw();
		}
		});

		var lastCol = table.columns().count() - 1;

		$(document).on('click', '#filter-empty', function () {
			table.column(lastCol).search('^$', true, false).draw();
		});

		$(document).on('click', '#filter-nonempty', function () {
			table.column(lastCol).search('.+', true, false).draw();
		});
});
</script>