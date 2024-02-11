const PROCEDURE = 0;
const PRODUCT = 1;
const PRODUCT_BARCODE = 2;

var default_volume_procedures = 1;

function financial(x) {
	return Number.parseFloat(x).toFixed(2);
}
  
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
    if (suggestion.stock != null && suggestion.stock)
    {
		if (suggestion.stock.length > 1) {
			$("#stock_select").append(new Option(suggestion.stock[0].lotnr, suggestion.stock[0].id, true, true));
		}
		else
		{
			var stock = suggestion.stock;
			var no_valid_location = true;

			// should not be needed, but just to be sure
			stock.sort((a, b) => {
				if (current_location === a.location && current_location === b.location) {
					// return parseFloat(a.volume) - parseFloat(b.volume);
					return new Date(a.eol) - new Date(b.eol);
				} else if (current_location === a.location) {
					return -1;
				} else if (current_location === b.location) {
					return 1;
				} else {
					return 0;
				}
			});
			
			stock.forEach(s => {
				const option = new Option(`${s.lotnr} (${parseFloat(s.volume).toPrecision()}) ${s.eol}`, s.id);
			
				if (current_location === s.location) {
					$("#stock_select").append(option);
					no_valid_location = false;
				} else {
					option.setAttribute("class", "bg-warning");
					$("#stock_select").append(option);
				}
			});
			if (no_valid_location) {
				$("#stock_select").addClass("is-invalid");
			}
		}
    }

    $("volume").focus();
}


function add_line()
{
    $.ajax({
		method: 'POST',
		url: URL_ADD_LINE + $("#product_or_proc").val(),
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
				$("#remove_event_button").hide();
				if(parsedData.vaccin == 1) { 
					// reload page
					location.reload();
				}
			} catch (error) {
				console.log(error);
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
}

function add_table_line(info)
{       
	if(info.type == PROCEDURE) 
	{
		var newRowHtml = `
			<tr>
				<td>
					<span class="d-md-none">${info.volume}x</span>
					${info.name}
				</td>
				<td class="d-none d-sm-table-cell">
					<div class="input-group input-group-sm" style="width:125px;">
						<input type="text" name="volume" value="${info.volume}" class="form-control" disabled="">
						<div class="input-group-append">
							<span class="input-group-text">st</span>
						</div>
					</div>
				</td>
				<td class="d-none d-sm-table-cell">&nbsp;</td>
				<td class="d-none d-sm-table-cell">${info.btw}%</td>
				<td>${financial(info.brut_price)} &euro;
					<span class="d-md-none">
					<a href="${URL_DELETE_PROC}${info.return}" class='btn btn-outline-danger btn-sm'><i class='fas fa-trash-alt'></i></a>
					</span>
				</td>
				<td class="d-none d-sm-table-cell"><small>${financial(info.net_price)} &euro;</small></td>
				<td class="d-none d-sm-table-cell"><a href="${URL_DELETE_PROC}${info.return}" class='btn btn-outline-danger btn-sm'><i class='fas fa-trash-alt'></i></a></td>
			</tr>
		`;
	}
	else
	{
		const lotnr = info.stock_lotnr ? `${info.stock_lotnr} - ${info.stock_eol}` : "&nbsp;";
		var newRowHtml = `
			<tr>
				<td>${info.name}</td>
				<td class="d-none d-sm-table-cell">
					<div class="input-group input-group-sm" style="width:125px;">
						<input type="text" name="volume" value="${info.volume}" class="form-control" disabled="">
						<div class="input-group-append">
							<span class="input-group-text">st</span>
						</div>
					</div>
				</td>
				<td class="d-none d-sm-table-cell"><small>${lotnr}</small></td>
				<td class="d-none d-sm-table-cell">${info.btw}%</td>
				<td>${financial(info.brut_price)} &euro;
					<span class="d-md-none">
					<a href="${URL_DELETE_PROD}${info.return}" class='btn btn-outline-danger btn-sm'><i class='fas fa-trash-alt'></i></a>
					</span>
				</td>
				<td class="d-none d-sm-table-cell"><small>${financial(info.net_price)} &euro;</small></td>
				<td class="d-none d-sm-table-cell"><a href="${URL_DELETE_PROD}${info.return}" class='btn btn-outline-danger btn-sm'><i class='fas fa-trash-alt'></i></a></td>
			</tr>
		`;

	}
	
	// Insert the new row after the first row
	$("#invoice_table tbody tr:first").after(newRowHtml);

	// add the brut and netto to the screen
	$("#bruto_sum").html(financial(parseFloat($("#bruto_sum").html()) + parseFloat(info.brut_price), 2));
	$("#netto_sum").html(financial(parseFloat($("#netto_sum").html()) + parseFloat(info.net_price), 2));
}

function reset_input()
{
    $("#new_pid, #product_or_proc, #volume, #hidden_booking, #stock_select, #vaccin_or_no, #vaccin_freq").val("");
	$('#autocomplete').val('').autocomplete('onValueChange').focus();
	$("#stock_select").removeClass("is-invalid");
}


document.addEventListener("DOMContentLoaded", function(){

    const current_location = $('#current_location_vet').val();

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
		serviceUrl: URL_PROC_OR_PROD,
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
				// by default sends 1 =s
				$("#hidden_booking").val("");
				
			// its not a product
			if (data.type == PROCEDURE)
			{
                event_set_procedure();
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
		autoSelectFirst: true,
		showNoSuggestionNotice: true,
		minChars: '2',
		deferRequestBy: 100
	});


    // add a line when button is clicked
    $("#add_line").click(function() {
		add_line();
    });

	// if on enter we want to push the line
    $("#volume").on("keydown", function(event) {
        if (event.which === 13) {
			add_line();
        }
    });

	// hide netto
	// since clients see this value as "to pay" so we hide it
	$(".sensitive").hover(function() {
		$(this).toggleClass('sensitive', 300);
	});
});
