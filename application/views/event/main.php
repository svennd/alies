<div class="row">
	<div class="col-lg-7 col-xl-10">

		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo $pet['name'] ?></a> <small>(#<?php echo $pet['id']; ?>)</small> / Event
			</div>
			<div class="card-body">
				<?php $total = 0; ?>
				<table class="table">
				<thead>
					<tr class="thead-light">
						<th>Name</th>
						<th>Barcode/LotNr</th>
						<th>Price</th>
						<th>Volume</th>
						<th>btw</th>
						<th>Price</th>
						<th>Options</th>
					</tr>
				</thead>
				<tbody>
				<?php include "event/block_procedures.php"; ?>
				<?php include "event/block_consumables.php"; ?>
				<?php include "event/block_add_prod_proc.php"; ?>
				<?php include "event/block_add_barcode.php"; ?>
				</tbody>
				<tfoot>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><i>Sum</i></td>
						<td><i><?php echo $total; ?></i></td>
						<td>&nbsp;</td>
					</tr>
				</tfoot>
				</table>

				<?php if($consumables || $procedures_d): ?>
					<?php if($event_info['payment'] == 0) : ?>
						<a href="<?php echo base_url(); ?>invoice/bill/<?php echo $owner['id']; ?>/<?php echo $event_id; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> Create invoice</a>
					<?php else: ?>
						<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> Show bill</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php include "event/block_report.php"; ?>
	</div>
	<div class="col-lg-5 col-xl-2">
		<?php include "event/block_client.php"; ?>
		<?php include "event/block_other_pets.php"; ?>
		<?php include "event/block_birthday.php"; ?>
		<?php include "event/block_event_controller.php"; ?>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#show_booking_select").click(function() {
		$("#show_booking_select").hide();
		$("#booking_select").show();
	});
	$("#barcode_show_booking_select").click(function() {
		$("#barcode_show_booking_select").hide();
		$("#barcode_booking_select").show();
	});

	$('#barcode_field').on('input', function() {
		var barcode = $("#barcode_field").val();
		if (barcode.length >= 5)
		{
			$.getJSON("<?php echo base_url(); ?>products/get_product_by_barcode?loc=<?php echo $u_location; ?>&barcode=" + barcode , function(data, status){

				$("#name_by_barcode").val(data.products.name);
				$("#barcode_new_pid").val(data.product_id);
				$("#barcode_barcode").val(data.barcode);
				$("#barcode_unit_sell").html("/ " + data.volume + " " + data.products.unit_sell);
				$("#barcode_btw_sell").val(data.products.btw_sell);
				$("#barcode_show_booking_select").html(data.products.btw_sell + "%");

				/* color & select the default booking code */
				var select = $('[id=barcode_hidden_booking] option[value="' + data.products.booking_code + '"]');
					select.addClass("bg-success");
					select.prop("selected", true)
			});
		}
	});

	$('#autocomplete').autocomplete({

		serviceUrl: '<?php echo base_url(); ?>products/get_product_or_procedure?loc=<?php echo $u_location; ?>',
		onSelect: function (suggestion) {
			// console.log(suggestion.data);
			$('#new_pid').val(suggestion.data.id);
			$('#prod').val(suggestion.data.prod);
			$("#btw_sell").val(suggestion.data.btw);
			$("#booking_default").val(suggestion.data.booking);
			$("#show_booking_select").html(suggestion.data.btw + "%");

			/* color the default booking code */
			var select = $('[id=hidden_booking] option[value="' + suggestion.data.booking + '"]');
				select.addClass("bg-success");
				select.prop("selected", true)

			// its not a product
			if (suggestion.data.prod == 0)
			{
				$('#unit_sell').html("st");
				$('#stock_select').prop('disabled', true);
				$("#stock_select").children().remove();
				$('#product_or_proc').val(0);
				$('#amount').val(1);

				// check if there is a price for a procedure
				if (suggestion.data.price != null)
				{
					$("#price_ajax_request").html(suggestion.data.price + " &euro;");
				}

			}
			else
			{
				if(suggestion.data.type == "barcode") {
					// set init
					$('#unit_sell').html(suggestion.data.unit);
					$('#stock_select').prop('disabled', false);
					$("#stock_select").children().remove();
					$('#product_or_proc').val(1);
					$('#vaccin_or_no').val(suggestion.data.vaccin);
					$('#vaccin_freq').val(suggestion.data.vaccin_freq);

					// there should only be one
					$("#stock_select").append(new Option(suggestion.data.barcode + " // " + suggestion.data.lotnr, suggestion.data.barcode, true, true));
					$('#unit_sell').html("/ " + suggestion.data.volume + " " + suggestion.data.unit);

					// check if there are prices (products)
					if (suggestion.data.prices != null)
					{
						prices_to_html(suggestion.data.prices, suggestion.data.unit);
					}
				}
				else {
					// product
					$('#unit_sell').html(suggestion.data.unit);
					$('#stock_select').prop('disabled', false);
					$("#stock_select").children().remove();
					$('#product_or_proc').val(1);
					$('#vaccin_or_no').val(suggestion.data.vaccin);
					$('#vaccin_freq').val(suggestion.data.vaccin_freq);

					// check if there is stock
					if (suggestion.data.stock != null)
					{
						var stock = "";

						// only 1 (preselect)
						if (suggestion.data.stock.length == 1)
						{
							stock = suggestion.data.stock[0];
							$("#stock_select").append(new Option(stock.barcode + " // " + stock.lotnr, stock.barcode, true, true));

							$('#unit_sell').html("/ " + stock.volume + " " + suggestion.data.unit);
						}
						// multiple
						else
						{
							// since this is not sorted take
							// current location first
							for (let i = 0; i < suggestion.data.stock.length; i++) {
								stock = suggestion.data.stock[i];

								if (<?php echo $u_location; ?> == stock.location)
								{
									var option = new Option(stock.barcode +" - " + stock.lotnr + " (" + parseFloat(stock.volume).toPrecision() + ")", stock.barcode);
									$("#stock_select").append(option);
								}
							}

							// other locations
							for (let i = 0; i < suggestion.data.stock.length; i++) {
								stock = suggestion.data.stock[i];

								if (<?php echo $u_location; ?> != stock.location)
								{
									var option = new Option(stock.barcode +" - " + stock.lotnr + " (" + parseFloat(stock.volume).toPrecision() + ")", stock.barcode);
									option.setAttribute("class", "bg-warning");
									$("#stock_select").append(option);
								}
							}
						}

					}
					// check if there are prices (products)
					if (suggestion.data.prices != null)
					{
						prices_to_html(suggestion.data.prices, suggestion.data.unit);
					}

				}
			}
		},
		groupBy: 'type',
		minChars: '2'
	});

});

function prices_to_html(prices, unit) {
	// only 1
	if (prices.length == 1)
	{
		prices = prices[0];
		$("#price_ajax_request").html(prices.price + " &euro; / " + prices.volume + " " + unit);
	}
	// multiple
	else
	{
		var min = parseFloat(prices[0].price);
		var max = parseFloat(prices[0].price);
		var loop = "<div class='collapse' id='collapseSELECT'><table class='small'>";

		for (let i = 0; i < prices.length; i++) {
			current_price = prices[i];
			loop += "<tr><td>" + current_price.volume + " " + unit + "</td><td>" + current_price.price + " &euro;</td></tr>";
			if (min > parseFloat(current_price.price)) { min = current_price.price; }
			if (max < parseFloat(current_price.price)) { max = current_price.price; }
		}
		loop += "</table></div>"

		$("#price_ajax_request").html("<a data-toggle='collapse' href='#collapseSELECT' role='button' aria-expanded='false' aria-controls='collapseSELECT'>" + min + " ~ " + max + " &euro;</a>" + loop);
	}
}
</script>
