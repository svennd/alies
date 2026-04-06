<style>
.bs-wizard {margin-bottom: 40px;}

/*Form Wizard*/
.bs-wizard {border-bottom: solid 1px #e0e0e0; padding: 0 0 10px 0;}
.bs-wizard > .bs-wizard-step {padding: 0; position: relative;}
.bs-wizard > .bs-wizard-step + .bs-wizard-step {}
.bs-wizard > .bs-wizard-step .bs-wizard-stepnum {color: #595959; font-size: 16px; margin-bottom: 5px;}
.bs-wizard > .bs-wizard-step .bs-wizard-info {color: #999; font-size: 14px;}
.bs-wizard > .bs-wizard-step > .bs-wizard-dot {position: absolute; width: 30px; height: 30px; display: block; background: #fbe8aa; top: 45px; left: 50%; margin-top: -15px; margin-left: -15px; border-radius: 50%;}
.bs-wizard > .bs-wizard-step > .bs-wizard-dot:after {content: ' '; width: 14px; height: 14px; background: #fbbd19; border-radius: 50px; position: absolute; top: 8px; left: 8px; }
.bs-wizard > .bs-wizard-step > .progress {position: relative; border-radius: 0px; height: 8px; box-shadow: none; margin: 20px 0;}
.bs-wizard > .bs-wizard-step > .progress > .progress-bar {width:0px; box-shadow: none; background: #fbe8aa;}
.bs-wizard > .bs-wizard-step.complete > .progress > .progress-bar {width:100%;}
.bs-wizard > .bs-wizard-step.active > .progress > .progress-bar {width:50%;}
.bs-wizard > .bs-wizard-step:first-child.active > .progress > .progress-bar {width:0%;}
.bs-wizard > .bs-wizard-step:last-child.active > .progress > .progress-bar {width: 100%;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot {background-color: #f5f5f5;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot:after {opacity: 0;}
.bs-wizard > .bs-wizard-step:first-child  > .progress {left: 50%; width: 50%;}
.bs-wizard > .bs-wizard-step:last-child  > .progress {width: 50%;}
.bs-wizard > .bs-wizard-step.disabled a.bs-wizard-dot{ pointer-events: none; }
/*END Form Wizard*/

</style>
<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>products">Products</a> / New Product
	</div>

	<div class="card-body">
		<div class="row bs-wizard" style="border-bottom:0;">

			<div class="col bs-wizard-step <?php if($step == 1): ?>active<?php elseif($step > 1): ?>complete<?php else: ?>disabled<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 1</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Product Info</div>
			</div>

			<div class="col bs-wizard-step <?php if($step < 2): ?>disabled<?php elseif($step == 2): ?>active<?php else: ?>complete<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 2</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Transaction</div>
			</div>

			<div class="col bs-wizard-step <?php if($step < 3): ?>disabled<?php elseif($step == 3): ?>active<?php else: ?>complete<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 3</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Limit</div>
			</div>

			<div class="col bs-wizard-step <?php if($step < 4): ?>disabled<?php elseif($step == 4): ?>active<?php else: ?>complete<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 4</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Advanced</div>
			</div>

			<div class="col bs-wizard-step <?php if($step < 5): ?>disabled<?php elseif($step == 5): ?>active<?php else: ?>complete<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 5</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Finished Overview</div>
			</div>
		</div>

		<?php if ($step == "1"): ?>
		<form action="<?php echo base_url(); ?>products/new" method="post" autocomplete="off">
			<h5>Product info</h5>
			<hr />
			<p><?php echo $this->lang->line('product_name_info'); ?></p>

			<div class="list-group mb-4 shadow">
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo $this->lang->line('product_name'); ?>*</strong>
							<p class="text-muted mb-0"><?php echo $this->lang->line('product_name_base_name'); ?></p>
						</div>
						<div class="col">
							<input type="text" class="form-control" id="product_name" name="name" value="" required>
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo $this->lang->line('product_wholesale'); ?></strong>
							<div class="btn btn-sm btn-outline-success ml-2" id="wholesale_button"><i class="fa-solid fa-link"></i></div>
						</div>
						<div class="col">
							<input type="text" class="form-control" id="input_wh_name" name="input_wh_name" value="">
							<input type="hidden" id="wholesale_id" name="wholesale" value="">
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo $this->lang->line('type'); ?></strong>
						</div>
						<div class="col-auto">
							<select name="type" class="form-control" id="type">
								<?php foreach($type as $t):?>
									<option value="<?php echo $t['id']; ?>"><?php echo html_escape(ucfirst($t['display_name'])); ?></option>
								<?php endforeach; ?>
								<option value="0">Other</option>
							</select>
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo $this->lang->line('vhbcode'); ?></strong>
						</div>
						<div class="col-auto">
							<input type="text" class="form-control" id="vhbcode" name="vhbcode" value="">
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0">CNK</strong>
							<div class="btn btn-sm btn-outline-success ml-2" id="cnk_button"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
						</div>
						<div class="col-auto">
							<input type="text" class="form-control" id="cnk" name="cnk" value="">
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0">CTI-e</strong>
						</div>
						<div class="col-auto">
							<input type="text" class="form-control" id="cti-e" name="cti_e" value="">
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><i class="fas fa-barcode"></i> <?php echo $this->lang->line('gs1_barcode'); ?></strong>
							<p class="text-muted mb-0"><?php echo $this->lang->line('gs1_scan_explain'); ?></p>
						</div>
						<div class="col-md-4">
							<div class="d-flex">
								<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" placeholder="scan code here">
								<input type="text" name="input_barcode" class="form-control ml-2" id="input_barcode" value="">
							</div>
							<small class="form-text text-muted" id="extra_info">Set the barcode manually</small>
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo ucfirst($this->lang->line('supplier')); ?></strong>
						</div>
						<div class="col-auto">
							<input type="text" class="form-control" id="supplier" name="supplier" value="">
						</div>
					</div>
				</div>
				<div class="list-group-item list-group-item-action">
					<div class="row align-items-center">
						<div class="col">
							<strong class="mb-0"><?php echo $this->lang->line('producer'); ?></strong>
						</div>
						<div class="col-auto">
							<input type="text" class="form-control" id="input_producer" name="producer" value="">
						</div>
					</div>
				</div>
			</div>

			<div class="text-right">
				<button type="submit" name="submit" value="add" class="btn btn-outline-success">Next</button>
			</div>
		</form>

		<?php elseif ($step == "2" && $product): ?>
		<form method="post" action="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>" autocomplete="off">
			<h5>Transaction</h5>
			<hr />
			<?php include 'block/edit_transaction.php'; ?>
			<div class="d-flex justify-content-between">
				<a href="<?php echo base_url(); ?>products/new/1/<?php echo $product['id']; ?>" class="btn btn-outline-secondary disabled" tabindex="-1" aria-disabled="true">Previous</a>
				<button type="submit" name="submit" value="store_transaction" class="btn btn-outline-success">Next</button>
			</div>
		</form>

		<?php elseif ($step == "3" && $product): ?>
		<form method="post" action="<?php echo base_url(); ?>products/new/3/<?php echo $product['id']; ?>" autocomplete="off">
			<h5>Limit</h5>
			<hr />
			<?php include 'block/edit_limiet.php'; ?>
			<div class="d-flex justify-content-between">
				<a href="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>" class="btn btn-outline-secondary">Previous</a>
				<button type="submit" name="submit" value="store_limit" class="btn btn-outline-success">Next</button>
			</div>
		</form>

		<?php elseif ($step == "4" && $product): ?>
		<form method="post" action="<?php echo base_url(); ?>products/new/4/<?php echo $product['id']; ?>" autocomplete="off">
			<h5>Advanced</h5>
			<hr />
			<?php include 'block/edit_advanced.php'; ?>
			<div class="d-flex justify-content-between">
				<a href="<?php echo base_url(); ?>products/new/3/<?php echo $product['id']; ?>" class="btn btn-outline-secondary">Previous</a>
				<button type="submit" name="submit" value="store_advanced" class="btn btn-outline-success">Finish</button>
			</div>
		</form>

		<?php elseif ($step == "5" && $product): ?>
		<div class="alert alert-success" role="alert">
		  <h4 class="alert-heading">Product added!</h4>
		  <p>Product <i><?php echo $product['name']; ?></i> is now added.</p>
		</div>

		<div class="row">
			<div class="col-md-6 mb-3">
				<div class="card shadow-sm h-100">
					<div class="card-header">Product info</div>
					<div class="card-body">
						<p class="mb-1"><strong>Name:</strong> <?php echo html_escape($product['name']); ?></p>
						<p class="mb-1"><strong>Wholesale:</strong> <?php echo html_escape($product['wholesale_name']); ?></p>
						<p class="mb-1"><strong>Supplier:</strong> <?php echo html_escape($product['supplier']); ?></p>
						<p class="mb-1"><strong>Producer:</strong> <?php echo html_escape($product['producer']); ?></p>
						<p class="mb-1"><strong>Type:</strong> <?php echo isset($product['type']['display_name']) ? html_escape($product['type']['display_name']) : ''; ?></p>
						<p class="mb-1"><strong>VHB:</strong> <?php echo html_escape($product['vhbcode']); ?></p>
						<p class="mb-1"><strong>CNK:</strong> <?php echo html_escape($product['cnk']); ?></p>
						<p class="mb-0"><strong>CTI-e:</strong> <?php echo html_escape($product['cti_e']); ?></p>
					</div>
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="card shadow-sm h-100">
					<div class="card-header">Transaction & settings</div>
					<div class="card-body">
						<p class="mb-1"><strong>Buy:</strong> <?php echo html_escape($product['buy_volume'] . ' ' . $product['unit_buy']); ?></p>
						<p class="mb-1"><strong>Sell:</strong> <?php echo html_escape($product['sell_volume'] . ' ' . $product['unit_sell']); ?></p>
						<p class="mb-1"><strong>Buy BTW:</strong> <?php echo html_escape($product['btw_buy']); ?>%</p>
						<p class="mb-1"><strong>Sellable:</strong> <?php echo !empty($product['sellable']) ? 'Yes' : 'No'; ?></p>
						<p class="mb-1"><strong>Discontinued:</strong> <?php echo !empty($product['discontinued']) ? 'Yes' : 'No'; ?></p>
						<p class="mb-1"><strong>Global limit:</strong> <?php echo html_escape($product['limit_stock']); ?></p>
						<p class="mb-0"><strong>Dead volume:</strong> <?php echo html_escape($product['dead_volume']); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="d-flex flex-wrap">
			<a href="<?php echo base_url(); ?>stock/add_stock/<?php echo $product['id']; ?>" class="btn btn-outline-success mr-2 mb-2">Add stock</a>
			<a href="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" class="btn btn-outline-primary mr-2 mb-2">Set pricing</a>
			<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>" class="btn btn-outline-secondary mr-2 mb-2">Open full edit page</a>
			<a href="<?php echo base_url(); ?>products/new" class="btn btn-outline-dark mb-2">Add another product</a>
		</div>
		<?php else: ?>
		<div class="alert alert-danger" role="alert">Product not found.</div>
		<?php endif;?>
    </div>
</div>




<script type="text/javascript">

function process_datamatrix(barcode) {

	// GS1 data matrix
	// 01 05420036903635 17 210400 10 111219
	// length : ~30
	// 01 EAN/GTIN  (14 length)
	// 17 YY MM DD date (6 length)
	// 10 barcode (variable length)
	// 6 + 14 + 6 + x

	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})17([0-9]{6})10(.*)/);
		if(result)
		{
			var input_barcode = result[1];
			var date = result[2];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);

			$("#input_barcode").val(result[1]);
			$("#extra_info").html("Scanned LotNR : " + "20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day + " lotnr :" + result[3]);
		}
	}
	else
	{
		console.log("code to short not recognized");
	}
}

function toggleByCheckbox(checkbox, target) {
	if ($(checkbox).length === 0) return;

	if ($(checkbox).is(':checked')) {
		$(target).show();
	} else {
		$(target).hide();
	}

	$(checkbox).on('change', function () {
		$(this).is(':checked')
			? $(target).slideDown()
			: $(target).slideUp();
	});
}

function diff(label, oldVal, newVal) {
	oldVal = oldVal || "(empty)";
	newVal = newVal || "(empty)";
	return oldVal === newVal
		? "<b>" + label + ":</b> " + oldVal + " <span style='color:green'>(same)</span><br>"
		: "<b>" + label + ":</b> " + oldVal + " → <b style='color:#d63384'>" + newVal + "</b><br>";
}

function buildDiff(map) {
	var html = "";
	$.each(map, function(_, field) {
		if (!field.new) return;
		html += diff(field.label, field.old, field.new);
	});
	return html || "No changes detected.";
}

function applyChanges(map) {
	$.each(map, function(_, field) {
		if (field.new && field.old !== field.new) {
			$(field.selector).val(field.new).addClass("is-valid");
		}
	});
}

document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
	toggleByCheckbox('#vaccin', '.no_vaccin_hide');
	toggleByCheckbox('#is_antibiotic', '.no_antibiotic_hide');

	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);

		}, 500);
	});

	$("#cnk_button").on("click", function(){
		var cnk = $("#cnk").val().trim();
		if (!cnk) return;

		var btn = $(this);
		var original = btn.html();

		btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop("disabled", true);

		$.ajax({
			url: '/fagg/api/fagg/by-cnk/' + cnk,
			type: 'GET',
			headers: {'X-API-Key':'2a0a05b5c83d3b15d537174eabe0be09ba6fb52097d41ed318663c78cff5ac98'},
			success: function(response){
				const item = response && response.data ? response.data : response;

				var map = {
					vhb: {
						label: "VHB",
						selector: "#vhbcode",
						old: $("#vhbcode").val(),
						new: item.VHB || ""
					},
					cti: {
						label: "CTI Extended",
						selector: "#cti-e",
						old: $("#cti-e").val(),
						new: item.cti_extended || ""
					},
					barcode: {
						label: "Barcode",
						selector: "#input_barcode",
						old: $("#input_barcode").val(),
						new: item.fdm || ""
					}
				};

				Swal.fire({
					title: "FAGG Update",
					html: buildDiff(map),
					showCancelButton: true,
					confirmButtonText: "Apply"
				}).then(function(result){
					if (result.isConfirmed) applyChanges(map);
				});
			},
			error: function(xhr, status, error){
				Swal.fire({
					icon: "error",
					title: "API Error",
					html: "<pre style='white-space:pre-wrap;text-align:left'>" +
						JSON.stringify({status: status, error: error, responseText: xhr.responseText}, null, 2) +
						"</pre>"
				});
			},
			complete: function(){
				btn.html(original).prop("disabled", false);
			}
		});
	});

	$("#wholesale_button").on("click", function(){
		Swal.fire({
			title: "Search article",
			html: '<select id="swal_wholesale" style="width:100%"></select>',
			showCancelButton: true,
			confirmButtonText: "Select",
			didOpen: function(){
				$('#swal_wholesale').select2({
					dropdownParent: $('.swal2-container'),
					theme: 'bootstrap4',
					placeholder: 'Select Article',
					ajax: {
						url: '<?php echo base_url("wholesale/ajax_get_articles"); ?>',
						dataType: 'json'
					}
				});
			},
			preConfirm: function(){
				return $('#swal_wholesale').select2('data')[0];
			}
		}).then(function(result){
			if (!result.isConfirmed || !result.value) return;

			var data = result.value;
			var map = {
				wholesale_name: {
					label: "Wholesale",
					selector: "#input_wh_name",
					old: $("#input_wh_name").val(),
					new: data.text || ""
				},
				vhb: {
					label: "VHB",
					selector: "#vhbcode",
					old: $("#vhbcode").val(),
					new: data.vhb || ""
				},
				cnk: {
					label: "CNK",
					selector: "#cnk",
					old: $("#cnk").val(),
					new: data.cnk || ""
				},
				producer: {
					label: "Producer",
					selector: "#input_producer",
					old: $("#input_producer").val(),
					new: data.distr || ""
				},
				supplier: {
					label: "Supplier",
					selector: "#supplier",
					old: $("#supplier").val(),
					new: data.distr || ""
				},
				id: {
					label: "Wholesale ID",
					selector: "#wholesale_id",
					old: $("#wholesale_id").val(),
					new: data.id || ""
				}
			};

			Swal.fire({
				title: "Confirm change",
				html: buildDiff(map),
				showCancelButton: true,
				confirmButtonText: "Apply"
			}).then(function(confirm){
				if (confirm.isConfirmed) applyChanges(map);
			});
		});
	});

});
</script>
