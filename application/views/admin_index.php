<div class="row"> 
 <div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Clients</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                   </div>
                </div>
              </div>
            </div>       

			<div class="col-xl-4 col-lg-5">
              <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Pets type</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieCharta"></canvas>
                   </div>
                </div>
              </div>
            </div>
</div>
		
<?php

function get_type($type)
{
			switch ($type)
			{
				case "0": $name = "dog"; break; # dog
				case "1": $name = "cat"; break; # cat
				case "2": $name = "horse"; break; # horse
				case "3": $name = "bird"; break; # bird
				default : # other
					$name = "anders";
			}
	return $name;
}
?>
<table>		
			<?php foreach ($oldest_ages as $pet): ?>
			<tr>
				<td><?php echo $pet['id']; ?></td>
				<td><?php echo get_type($pet['type']); ?></td>
				<td><?php echo $pet['breeds']['name']; ?></td>
				<td><?php echo $pet['name']; ?></td>
				<td><?php echo timespan(strtotime($pet['birth']), time(), 1); ?></td>
			</tr>
			<?php endforeach; ?>
</table>

<script>
<?php 
	$group_names = json_encode(array_keys($per_city));
	$group_values = json_encode(array_values($per_city));
	$a = json_encode($per_pet_type['type']);
	$b = json_encode($per_pet_type['amount']);
?>
var result = {
    labels: <?php echo $group_names; ?>,
    datasets: [{
      data: <?php echo $group_values; ?>,
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f'],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  };
var resulta = {
    labels: <?php echo $a; ?>,
    datasets: [{
      data: <?php echo $b; ?>,
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f5c6cb', '#fbdc8f'],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#ff6575', '#f6c23e'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  };
  
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#usermgm").addClass('active');
	$("#adminstat").addClass('active');
	
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
    cutoutPercentage: 80,
  },
});

// Pie Chart Example
var ctxa = document.getElementById("myPieCharta");
var myPieCharta = new Chart(ctxa, {
  type: 'doughnut',
  data: resulta,
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
    cutoutPercentage: 80,
  },
});

});

</script>