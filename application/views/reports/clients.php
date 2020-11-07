<div class="row"> 
	<div class="col-lg-10 mb-4">


		<div class="card shadow mb-4">
			<div class="card-header">
				Report / Export clients (<?php echo $total_clients; ?>)
			</div>

			<div class="card-body">
				<a href="<?php echo base_url(); ?>export/clients/30" class="btn btn-success" download><i class="fas fa-file-export"></i> 30 days</a> 
				<a href="<?php echo base_url(); ?>export/clients/90" class="btn btn-success" download><i class="fas fa-file-export"></i> 90 days</a>
				<a href="<?php echo base_url(); ?>export/clients" class="btn btn-warning ml-3" download><i class="fas fa-file-export"></i> All Clients</a>
				<br>
				<small>Exports only created & updated</small>
			</div>
		</div>		
	</div>
	
	<div class="col-lg-2 mb-4">

	</div>
</div>
<script>
<?php 
	$group_names = json_encode(array_keys($per_city));
	$group_values = json_encode(array_values($per_city));
	$group_names_prov = json_encode(array_keys($per_province));
	$group_values_prov = json_encode(array_values($per_province));
?>
var line_chart_data = { 
						labels: <?php echo $last_bill['labels']; ?>, 
						datasets: [{ 
									data:<?php echo $last_bill['values']; ?>,   
									label: "Last Bill",
									lineTension: 0.3,
									backgroundColor: "rgba(78, 115, 223, 0.05)",
									borderColor: "rgba(78, 115, 223, 1)",
									pointRadius: 3,
									pointBackgroundColor: "rgba(78, 115, 223, 1)",
									pointBorderColor: "rgba(78, 115, 223, 1)",
									pointHoverRadius: 3,
									pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
									pointHoverBorderColor: "rgba(78, 115, 223, 1)",
									pointHitRadius: 10,
									pointBorderWidth: 2,
									}] 
						};
var init_vets = { 
						labels: <?php echo $vets['5y']['labels']; ?>, 
						datasets: [
									{ 
									data:<?php echo $vets['5y']['values']; ?>,   
									label: "5y",
										"backgroundColor" :[
															"rgba(255, 99, 132, 0.8)",
															"rgba(75, 192, 192, 0.8)",
															"rgba(255, 205, 86, 0.8)",
															"rgba(201, 203, 207, 0.8)",
															"rgba(54, 162, 235, 0.8)",
															"rgba(78, 115, 223, 0.8)",
														]
									},
								] 
						};
var init_loc = { 
					labels: <?php echo $locations['5y']['labels']; ?>, 
					datasets: [
								{ 
								data:<?php echo $locations['5y']['values']; ?>,   
								label: "5y",
									"backgroundColor" :[
														"rgba(255, 99, 132, 0.8)",
														"rgba(75, 192, 192, 0.8)",
														"rgba(255, 205, 86, 0.8)",
														"rgba(201, 203, 207, 0.8)",
														"rgba(54, 162, 235, 0.8)",
														"rgba(78, 115, 223, 0.8)",
													]
								},
							] 
					};
var result = {
    labels: <?php echo $group_names; ?>,
    datasets: [{
      data: <?php echo $group_values; ?>,
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f',],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e',],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  };
var resulta = {
    labels: <?php echo $group_names_prov; ?>,
    datasets: [{
      data: <?php echo $group_values_prov; ?>,

      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f','#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f',],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e','#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e',],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }
	],
  };
  
document.addEventListener("DOMContentLoaded", function(){
	$("#reports").addClass('active');
	
  // Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: result,
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 50,
  },
});
var ctxa = document.getElementById("myPieCharta");
var myPieCharta = new Chart(ctxa, {
  type: 'doughnut',
  data: resulta,
  options: {
    maintainAspectRatio: false,
	borderWidth : 5,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 50,
  },
});


var InitVet = document.getElementById("InitVet");
InitVet.height = 200;
var myPieCharta = new Chart(InitVet, {
  type: 'bar',
  data: init_vets,
  options: {},
});

var InitLoc = document.getElementById("InitLoc");
InitLoc.height = 200;
var myPieCharta = new Chart(InitLoc, {
  type: 'polarArea',
  data: init_loc,
  options: {},
});

	var linec = document.getElementById("LineChart");
	var myLineChart = new Chart(linec, {
		type: 'line',
		data: line_chart_data,
		options: {}
	});

});

</script>