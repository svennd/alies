<?php
// determ if we are editing or adding a pet profile
$edit_mode = (isset($pet)) ? true : false;
?>

<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="card shadow mb-4">
			<div class="card-header">
				<?php if(!$edit_mode): ?>
					<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / Add pet
				<?php else: ?>
					<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
					<?php if($pet['death'] == 1 || $pet['lost'] == 1): ?>
					<?php echo (isset($pet['name'])) ? $pet['name']: '' ?>
					<?php else : ?>
					<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo (isset($pet['name'])) ? $pet['name']: '' ?></a>
					<?php endif; ?>
					/ Edit pet
				<?php endif; ?>
			</div>
			<div class="card-body">
<form action="<?php echo ($edit_mode) ?
						base_url() . 'pets/edit/' . $pet['id'] :
						base_url() . 'pets/add/' . $owner['id']; ?>" method="post" autocomplete="off">

<?php include 'profile/required.php'; ?>
<?php include 'profile/details.php'; ?>

</div>
		</div>

	</div>
	<div class="col-lg-5 col-xl-2">
		<?php include "fiche/block_full_client.php"; ?>
	</div>
</div>

<script type="text/javascript">
// based on ISO 11784
// info : https://www.icar.org/index.php/rfid-injectable/
// info : https://www.albertaanimalhealthsource.ca/sites/default/files/uploads/manufacturersisosandcountrycodes.pdf
// info : https://github.com/Proxmark/proxmark3/blob/master/client/cmdlffdx.c
// info : https://www.pet-detect.com/pages/Interpreting-microchip-numeric-codes.aspx?pageid=610 (france)
// country code:  https://nl.wikipedia.org/wiki/ISO_3166-1 ???
var country_code = [];
	// incomplete list
	country_code[32] = "Argentina";
	country_code[36] = "Australia";
	country_code[40] = "Austria";
	country_code[44] = "Bahamas";
	country_code[52] = "Barbados";
	country_code[56] = "Belgium";
	country_code[60] = "Bermuda";
	country_code[76] = "Brazil";
	country_code[100] = "Bulgaria";
	country_code[124] = "Canada";
	country_code[152] = "Chile";
	country_code[156] = "China";
	country_code[158] = "Taiwan";
	country_code[203] = "Czech Republic";
	country_code[208] = "Denmark";
	country_code[214] = "Dominican Republic";
	country_code[246] = "Finland";
	country_code[250] = "France";
	country_code[276] = "Germany";
	country_code[300] = "Greece";
	country_code[348] = "Hungary";
	country_code[356] = "India";
	country_code[360] = "Indonesia";
	country_code[372] = "Ireland";
	country_code[376] = "Israel";
	country_code[380] = "Italy";
	country_code[392] = "Japan";
	country_code[442] = "Luxembourg";
	country_code[458] = "Malaysia";
	country_code[484] = "Mexico";
	country_code[492] = "Monaco";
	country_code[528] = "Netherlands";
	country_code[554] = "New Zealand";
	country_code[578] = "Norway";
	country_code[604] = "Peru";
	country_code[608] = "Philippines";
	country_code[616] = "Poland";
	country_code[620] = "Portugal";
	country_code[630] = "Puerto Rico";
	country_code[642] = "Romania";
	country_code[643] = "Russian Federation";
	country_code[710] = "South Africa";
	country_code[724] = "Spain";
	country_code[752] = "Sweden";
	country_code[756] = "Switzerland";
	country_code[764] = "Thailand";
	country_code[792] = "Turkey";
	country_code[804] = "Ukraine";
	country_code[818] = "Egypt";
	country_code[826] = "United Kingdom";
	country_code[840] = "Unite States";
	country_code[858] = "Uruguay";
	country_code[862] = "Venezuela";
	country_code[891] = "Yogoslavia";

function make_date(date)
{
	if($("#dead").prop("checked") == true) { return false; }
	if (!date) {return false;}
	var today = new Date();
	var birthDate = new Date(date);
	var years = (today.getFullYear() - birthDate.getFullYear());
	if (today.getMonth() < birthDate.getMonth() ||
		today.getMonth() == birthDate.getMonth() && today.getDate() < birthDate.getDate()) {
		years--;
	}
	if (isNaN(years))
	{
		$("#birth_info").html("Wrong date!");
	}
	else
	{
		$("#birth_info").html(years + " years old");
	}
}

function get_chip_info(chip)
{
	if (!chip) {return false;}
	if(chip.toString().length == 15)
	{
		if (chip.substr(0,1) == 9)
		{
			$("#chip_info").html("manufacture code");
		}
		else
		{
			if(typeof country_code[chip.substr(0,3)] === 'undefined')
			{
				$("#chip_info").html("Unknown country code");
			}
			else
			{
				$("#chip_info").html("Country code : " + country_code[chip.substr(0,3)]);
			}
		}
	}
	else
	{
		$("#chip_info").html("Unrecognized code, not 15 numbers!");
	}
}

document.addEventListener("DOMContentLoaded", function(){
	$("#breeds").select2();
	$("#birth").change(function() {
		make_date(this.value);
	});
	$("#chip").change(function() {
		get_chip_info(this.value);
	});

	$("dead").change(function(event) {
		var checkbox = event.target;
		if (checkbox.checked) {
		} else {

		}
	});

	make_date($("#birth").val());
	get_chip_info($("#chip").val());
});
</script>
