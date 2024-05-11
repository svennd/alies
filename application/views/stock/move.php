<style>
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
</style>

<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url('products'); ?>">Stock</a> / <?php echo $this->lang->line('move'); ?>
			</div>
			<div class="card-body">
				<?php if($move_complete): ?>
					<div class="alert alert-success">Move complete !</div>
				<?php endif; ?>
				<h5>select location</h5>
				<div class="row">
					<div class="col">				
						<select name="from" class="form-control" id="location" autofocus>
								<option value="">---</option>
							<?php foreach($stocks as $stock): if($stock['id'] == $user_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col">		
						<select name="notupe" disabled class="form-control">
							<?php foreach($stocks as $stock): if($stock['id'] != $user_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<br/>
				<hr />
				<br/>
				<div id="nextPartOfForm" style="display: none;">
					<h5>Add products to move</h5>
					<div class="row justify-content-between" >
						<div class="col-md-4">
							<input type="text" name="product" class="form-control" style="width:250px;" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off">
							<br />
							<hr />
							<div id="product-details"></div>
						</div>

						<div class="col-md-6">
							<form action="<?php echo base_url('stock/move'); ?>" method="post" autocomplete="off" id="move_form" class="d-none">
								<div id="move_lines">
								</div>
								<div class="float-right">
									<input type="hidden" name="location_hidden" value="" id="location_hidden"/>
									<a href="<?php echo base_url('stock/move'); ?>" class="btn btn-sm btn-outline-danger ml-3">Annuleer</a>
									<button type="submit" name="submit" value="quantities" class="btn btn-outline-success">Verplaatsen</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
const URL_PRODUCTS_API = "<?php echo base_url('stock/get_product_stock'); ?>";
const LANG_AVAILABLE_STOCK = "<?php echo $this->lang->line('available_lotnr'); ?>";

function renderProductDetails(details) {
	const container = $("#product-details");

	var list_start 	= LANG_AVAILABLE_STOCK + '<div class="list-group mt-2">';
	var list_end	= '</div>';
	var productRow 	= '';

	details.id.forEach((id, index) => {
		productRow += `<a href="#" data-stock="${id}" data-unit="${details.unit}" data-name="${details.prod}" data-lotnr="${details.lotnr[index]}" data-eol="${details.eol[index]}" data-volume="${details.volume[index]}" class="list-group-item list-group-item-action stockitem" style="margin-bottom:0px;">
						<div class="d-flex w-100 justify-content-between">
							<h5 class="mb-1">${details.lotnr[index]}</h5>
							<small>volume: ${details.eol[index]}</small>
						</div>
						<p class="mb-1">${details.volume[index]} ${details.unit}</p>					
						</a>`;
	});

	container.append(list_start + productRow + list_end);
}

function create_move_list()
{
	const container = $("#move_lines");

	// for some reason this doesn't work if I put it in DOMContentLoaded scope
	// --> need to use event delegation, because the elements are created dynamically
	$('.stockitem').on('click', function() {

		const container = $("#move_lines");
		let prod = $(this).data('name');
		let stock = $(this).data('stock'); // id
		let lotnr = $(this).data('lotnr');
		let eol = $(this).data('eol');
		let unit = $(this).data('unit');
		let volume = $(this).data('volume');

		move_line = `
			<div class="form-row">
				<div class="col">
					<input type="text" readonly="" class="form-control-plaintext form-control-sm" value="${prod}">
					<small>${lotnr} - ${eol}</small>
				</div>
				<div class="col">
					<div class="input-group input-group-sm">
						<input type="text" class="form-control check_max" data-max-volume="${volume}" name="move_volume[${stock}]" required>
						<div class="input-group-append">
							<span class="input-group-text">${unit}</span>
							<span class="input-group-text">/ ${volume}</span>
						</div>
					</div>
				</div>
			</div>
		`;
		container.append(move_line);
		$('#move_form').removeClass('d-none');
		$("#product-details").empty();
		$('#autocomplete').val('').autocomplete().clear();
	});
}

document.addEventListener("DOMContentLoaded", function(){


	$("#move_stock").addClass('active');

	// init set
	let vlocation = $("#location").find(":selected").val();

	$('#location').on('change', function() {
		vlocation = $(this).find(":selected").val();
	});

	// event delegation
	$("#move_lines").on('focusout', '.check_max', function() {
		let max = parseFloat($(this).data('max-volume'));
		if(parseFloat($(this).val()) > max) {
			$(this).addClass('is-invalid');
		}
		else 
		{
			$(this).removeClass('is-invalid');
		}
	});

$('#autocomplete').autocomplete({
		onSearchStart: function (query) {
			$("#product-details").empty();
		},
		serviceUrl: function (el){
			return URL_PRODUCTS_API+ '/' + vlocation + '/';
		},
		onSelect: function (suggestion) {
			renderProductDetails(suggestion.data);
			create_move_list();
		},
		autoSelectFirst: true,
		showNoSuggestionNotice: true,
		minChars: '2'
	});

	// UI
	$('#location').change(function() {
		var selectedValue = $(this).val(); // Get the selected value
		$('#location_hidden').val(selectedValue); // Store the selected value in a hidden input
		$(this).prop('disabled', true); // Disable the dropdown
		$('#nextPartOfForm').show(); // Show the next part of the form
	});
});
</script>