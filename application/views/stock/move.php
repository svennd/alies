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
				<div class="row">
					<div class="col">				
						<select name="from" class="form-control" id="type">
							<?php foreach($stocks as $stock): if($stock['id'] == $this->user->current_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col">		
						<select name="notupe" disabled class="form-control">
							<?php foreach($stocks as $stock): if($stock['id'] != $this->user->current_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col"><input type="text" name="product" class="form-control form-control-sm" style="width:250px;" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off" autofocus></div>
					<div class="col">shortages in location?</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
const URL_PRODUCTS_API = "<?php echo base_url('products/get_product/search_stock'); ?>";

document.addEventListener("DOMContentLoaded", function(){

$('#autocomplete').autocomplete({
		serviceUrl: URL_PRODUCTS_API,
		onSelect: function (suggestion) {
            var data = suggestion.data;
			$('#new_pid').val(data.id);
			$('#prod').val(data.prod);
			$("#btw_sell").val(data.btw);
			$("#booking_default").val(data.booking);
			$("#show_booking_select").html(data.btw + "%");

			// we selected a product, now give me the amount!
			$("#volume").focus();

			/* color the default booking code */
			var select = $('[id=hidden_booking] option[value="' + data.booking + '"]');
				select.addClass("bg-success");

			// its not a product
			if (data.type == PROCEDURE)
			{
                event_set_procedure();
			}
			else if(data.type == PRODUCT_BARCODE) 
            {
                event_set_barcode(data);
            }
			else 
            {
                event_set_product(data, current_location);
			}
		},
		onSearchComplete: function (query, suggestion) { 
			// fire onselect if only 1 results
			if(suggestion.length == 1 && query.length > 10)
			{
				$(this).autocomplete().onSelect(0);
				$("#volume").focus();
			}
		},
        onInvalidateSelection: function() {
            // if we dont select anything, reset the input
            reset_input();
        },
		autoSelectFirst: true,
		showNoSuggestionNotice: true,
		minChars: '2'
	});
});
</script>