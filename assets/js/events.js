const PROCEDURE = 0;
const PRODUCT = 1;
const PRODUCT_BARCODE = 2;

var default_volume_procedures = 1;

function event_set_procedure()
{
    // no specific unit
    $('#unit_sell').html("st");
    
    // disable stock
    $("#stock_select").prop('disabled', true).children().remove();

    // define type
    $('#product_or_proc').val(PROCEDURE);

    // set default
    $('#volume').val(default_volume_procedures);
}

function event_set_barcode(suggestion)
{
    $('#unit_sell').html(suggestion.unit);
    $('#stock_select').prop('disabled', false);
    $('#product_or_proc').val(1);
    $('#vaccin_or_no').val(suggestion.vaccin);
    $('#vaccin_freq').val(suggestion.vaccin_freq);

    // there should only be one
    $("#stock_select").append(new Option(suggestion.lotnr, suggestion.id, true, true));
    $('#unit_sell').html("/ " + suggestion.volume + " " + suggestion.unit);
}

function event_set_product(suggestion, current_location)
{
    // product
    $('#unit_sell').html(suggestion.unit);

    // enable but clear
    $("#stock_select").prop('disabled', false).children().remove();

    // set type
    $('#product_or_proc').val(PRODUCT);

    // set vaccin
    $('#vaccin_or_no').val(suggestion.vaccin);
    $('#vaccin_freq').val(suggestion.vaccin_freq);

    // check if there is stock
    if (suggestion.stock != null)
    {
		var stock = suggestion.stock;
		
		stock.sort((a, b) => {
			if (current_location === a.location && current_location === b.location) {
				return parseFloat(a.volume) - parseFloat(b.volume);
			} else if (current_location === a.location) {
				return -1;
			} else if (current_location === b.location) {
				return 1;
			} else {
				return 0;
			}
		});
		
		stock.forEach(s => {
			const option = new Option(`${s.lotnr} (${parseFloat(s.volume).toPrecision()})`, s.id);
		
			if (current_location === s.location) {
				$("#stock_select").append(option);
			} else {
				option.setAttribute("class", "bg-warning");
				$("#stock_select").append(option);
			}
		});
    }

    $("volume").focus();
}


function add_line(current_event)
{
    $.ajax({
		method: 'POST',
		url: '../../events/add_line/' + current_event + '/' + $("#product_or_proc").val(),
		data: {
			line: $("#new_pid").val(),
			title: $("#autocomplete").val(),
			volume: $("#volume").val(),
			booking_default: $("#booking_default").val(),
			booking: $("#hidden_booking").val(),
			stock: $("#stock_select").val(),
			btw: $("#btw_sell").val(),
			vaccin: $("#vaccin_or_no").val(),
			vaccin_freq: $("#vaccin_freq").val(),
		},
		success: function(response) {
			try {
				var parsedData = JSON.parse(response);
				add_table_line(parsedData);
				enable_generate_invoice();
			} catch (error) {
				let errorhtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
				The response of the server failed, please <a href="javascript:location.reload();">REFRESH the page</a>.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				</div>`;
				$("#nav-home").before(errorhtml);
			}
			reset_input();
		},
		error: function(xhr, status, error) {
			let errorhtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Houston, we have a problem!</strong> Something didn't work. Please try refreshing.<br/>
				Technical error: ${error}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				</div>`;
			$("#nav-home").before(errorhtml);
		}
	});
}

function enable_generate_invoice()
{
	$('#generate_bill').removeClass('d-none');
	// $('#generate_bill').addClass('d-block');
}

function add_table_line(info)
{       
	if(info.type == PROCEDURE) 
	{
		var newRowHtml = `
			<tr>
				<td>${info.name}</td>
				<td>
					<div class="input-group input-group-sm" style="width:125px;">
						<input type="text" name="volume" value="${info.volume}" class="form-control" disabled="">
						<div class="input-group-append">
							<span class="input-group-text">st</span>
						</div>
					</div>
				</td>
				<td>&nbsp;</td>
				<td>${info.btw}%</td>
				<td>${info.brut_price.toFixed(2)} &euro;</td>
				<td><small>${info.net_price.toFixed(2)} &euro;</small></td>
			</tr>
		`;
	}
	else
	{
		var newRowHtml = `
			<tr>
				<td>${info.name}</td>
				<td>
					<div class="input-group input-group-sm" style="width:125px;">
						<input type="text" name="volume" value="${info.volume}" class="form-control" disabled="">
						<div class="input-group-append">
							<span class="input-group-text">st</span>
						</div>
					</div>
				</td>
				<td><small>${info.stock_lotnr} - ${info.stock_eol}</small></td>
				<td>${info.btw}%</td>
				<td>${info.brut_price.toFixed(2)} &euro;</td>
				<td><small>${info.net_price.toFixed(2)} &euro;</small></td>
			</tr>
		`;

	}
	
	// Insert the new row after the first row
	$("#invoice_table tbody tr:first").after(newRowHtml);
}

function reset_input()
{
    $("#autocomplete, #new_pid, #product_or_proc, #volume, #hidden_booking, #stock_select, #vaccin_or_no, #vaccin_freq").val("");
    $("#autocomplete").focus();
}


document.addEventListener("DOMContentLoaded", function(){

    const current_location = $('#current_location_vet').val();
    const current_event = $('#current_event').val();

	/* select the correct tab based on the url */
	var strHash = document.location.hash;
	if (strHash == "") {
		$("#headtabs a:first").addClass("active");
		$("#nav-tabContent-heads div:first").addClass("active");
	} else {
		$("a[href='" + strHash + "']").click();
	}

	/* grootboekhoud rekeningen */
	$("#show_booking_select").click(function() {
		$("#show_booking_select").hide();
		$("#booking_select").show();
	});

	// automatically put the input here
	$("#autocomplete").focus();

	// search box for products
	$('#autocomplete').autocomplete({
		serviceUrl: '../../products/get_product_or_procedure',
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


    // add a line when button is clicked
    $("#add_line").click(function() {
		add_line(current_event);
    });

	// if on enter we want to push the line
    $("#volume").on("keydown", function(event) {
        if (event.which === 13) {
			add_line(current_event);
        }
    });
});
