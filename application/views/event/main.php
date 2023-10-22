<style>
.depad_header th { padding: 0.15rem 0.75rem; }
.nav-borders .nav-link {
  color: #69707a;
  border-bottom-width: 0.125rem;
  border-bottom-style: solid;
  border-bottom-color: transparent;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 0;
  padding-right: 0;
  margin-left: 1rem;
  margin-right: 1rem;
}
.nav-borders .nav-link.active {
  color: #0061f2;
  border-bottom-color: #0061f2;
}
.nav-borders .nav-link.disabled {
  color: #c5ccd6;
}
.nav-borders.flex-column .nav-link {
  padding-top: 0;
  padding-bottom: 0;
  padding-right: 1rem;
  padding-left: 1rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  margin-right: 0;
  margin-left: 0;
  border-bottom: none;
  border-right-width: 0.125rem;
  border-right-style: solid;
  border-right-color: transparent;
}
.nav-link.active {
  border-right-color: #0061f2;
}


</style>
<div class="row">
	<div class="col-lg-12 col-xl-10">

		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
					<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo $pet['name'] ?></a> <small>(#<?php echo $pet['id']; ?>)</small> / Event
				</div>
				<?php include "event/block_header_types.php"; ?>
			</div>
			<div class="card-body">
					
				<nav class="nav nav-borders" id="headtabs">
					<!-- <a href="#index" class="nav-link" id="nav-home-tab" data-toggle="tab" data-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true"><?php echo $this->lang->line('check'); ?></a>
					<a href="#report" class="nav-link <?php echo ($event_info['no_history'] == 1) ? "disabled":""; ?>" id="nav-report-tab" data-toggle="tab" data-target="#nav-report" type="button" role="tab" aria-controls="nav-report" aria-selected="false"><?php echo $this->lang->line('report'); ?></a> -->
					<a href="#index" class="nav-link" id="nav-home-tab" data-toggle="tab" data-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="false"><?php echo $this->lang->line('check'); ?></a>
					<a href="#report" class="nav-link <?php echo ($event_info['no_history'] == 1) ? "disabled":""; ?>" id="nav-report-tab" data-toggle="tab" data-target="#nav-report" type="button" role="tab" aria-controls="nav-report" aria-selected="true"><?php echo $this->lang->line('report'); ?></a>
					<!-- <a href="#media" class="nav-link <?php echo ($event_info['no_history'] == 1) ? "disabled":""; ?>" id="nav-media-tab" data-toggle="tab" data-target="#nav-media" type="button" role="tab" aria-controls="nav-media" aria-selected="false"><?php echo $this->lang->line('media'); ?></a>
					<a href="#attachement" class="nav-link <?php echo ($event_info['no_history'] == 1) ? "disabled":""; ?>" id="nav-attachement-tab" data-toggle="tab" data-target="#nav-attachement" type="button" role="tab" aria-controls="nav-attachement" aria-selected="false"><?php echo $this->lang->line('files'); ?></a> -->
				</nav>
				<hr class="mt-0 mb-3">
				
				<div class="tab-content" id="nav-tabContent-heads">
					<div class="tab-pane" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
						<?php include "invoice_index.php"; ?>
					</div>
					<div class="tab-pane" id="nav-report" role="tabpanel" aria-labelledby="nav-report-tab">
						<?php include "event/block_report.php"; ?>
					</div>
					<!-- <div class="tab-pane" id="nav-media" role="tabpanel" aria-labelledby="nav-media-tab">
						<?php // include "event/block_drawing.php"; ?>
					</div>
					<div class="tab-pane" id="nav-attachement" role="tabpanel" aria-labelledby="nav-attachement-tab">
						<?php // include "event/block_attachments.php"; ?>
					</div> -->
				</div>
				<hr class="mt-0 mb-3">
				<?php if($consumables || $procedures_d): ?>
					<?php if($event_info['payment'] == 0) : ?>
						<a href="<?php echo base_url(); ?>invoice/bill/<?php echo $owner['id']; ?>/<?php echo $event_id; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> <?php echo $this->lang->line('create_bill'); ?></a>
					<?php else: ?>
						<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> <?php echo $this->lang->line('show_bill'); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="col-xl-2">
		<?php include "event/block_client.php"; ?>
		<?php include "event/block_other_pets.php"; ?>
		<?php include "application/views/pets/fiche/block_birth.php"; ?>
		<?php include "event/block_birthday.php"; ?>
		<?php include "event/block_event_controller.php"; ?>
	</div>

</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){

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

		serviceUrl: '<?php echo base_url(); ?>products/get_product_or_procedure?loc=<?php echo $u_location; ?>',
		onSelect: function (suggestion) {

			$('#new_pid').val(suggestion.data.id);
			$('#prod').val(suggestion.data.prod);
			$("#btw_sell").val(suggestion.data.btw);
			$("#booking_default").val(suggestion.data.booking);
			$("#show_booking_select").html(suggestion.data.btw + "%");

			// we selected a product, now give me the amount!
			$("#amount").focus();

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
			}
			else
			{
				if(suggestion.data.type == "barcode") {
					// set init
					$('#unit_sell').html(suggestion.data.unit);
					$('#stock_select').prop('disabled', false);
					$('#product_or_proc').val(1);
					$('#vaccin_or_no').val(suggestion.data.vaccin);
					$('#vaccin_freq').val(suggestion.data.vaccin_freq);

					// there should only be one
					$("#stock_select").append(new Option(suggestion.data.barcode + " // " + suggestion.data.lotnr, suggestion.data.barcode, true, true));
					$('#unit_sell').html("/ " + suggestion.data.volume + " " + suggestion.data.unit);
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
						var stock = suggestion.data.stock;

						// since this is not sorted take
						// current location first
						for (let i = 0; i < stock.length; i++) {
							let s = stock[i];

							if (<?php echo $u_location; ?> == s.location)
							{
								$("#stock_select").append(new Option(s.lotnr +" (" + parseFloat(s.volume).toPrecision() + ") - " + s.barcode, s.barcode));
								
							}
						}
						// other locations
						for (let i = 0; i < stock.length; i++) {
							let s = stock[i];

							if (<?php echo $u_location; ?> != s.location)
							{
								var option = new Option(s.lotnr +" (" + parseFloat(s.volume).toPrecision() + ") - " + s.barcode, s.barcode);
									option.setAttribute("class", "bg-warning");
								$("#stock_select").append(option);
							}
						}

					}

				}
			}
		},
		onSearchComplete: function (query, suggestion) { 
			// fire onselect if only 1 results
			if(suggestion.length == 1 && query.length > 10)
			{
				$(this).autocomplete().onSelect(0);
				$("#amount").focus();
			}
		},
		autoSelectFirst: true,
		showNoSuggestionNotice: true,
		minChars: '2'
	});

});

</script>
