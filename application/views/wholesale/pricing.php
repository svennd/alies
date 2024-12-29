<div class="row">

      <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
			    <div class="text-danger"><?php echo $this->lang->line('increase'); ?></div>
			</div>
            <div class="card-body">
                
				<?php if ($products): ?>
				<div class="alert alert-info mb-3" role="alert"><?php echo $this->lang->line('increase_wh_explain'); ?></div>
				<table class="table table-sm" id="dataTable">
				<thead>
                    <tr>
						<th><?php echo $this->lang->line('product'); ?></th>
                        <th><?php echo $this->lang->line('catalog_price'); ?></th>
                        <th><?php echo $this->lang->line('increase'); ?></th>
                    </tr>
				</thead>
				<tbody>
                    <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><a href="<?php echo base_url('pricing/prod/' . $prod['id']); ?>"><?php echo $prod['name']; ?></a></td>
                        <td><?php echo $prod['bruto']; ?> &euro;</td>
                        <td class="text-danger">+<?php echo $prod['percentage_change']; ?> %</td>
                    </tr>
                    <?php endforeach; ?>
				</tbody>
				</table>
				<?php else: ?>
					no increases detected.
				<?php endif; ?>
			</div>
		</div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#dataTable").DataTable(
        {
            "order": [[ 2, "desc" ]],
			scrollY:        '60vh',
			deferRender:    true,
			scroller:       true,
        }
    );
});
</script>