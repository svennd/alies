<h1 class="h3 mb-2 text-gray-800">Income</h1>
<hr>
<div class="row">
	<div class="col-md-8">
		<div class="card shadow mb-4">
			<div class="card-header">
				Total Revenue
			</div>
			<div class="card-body">
                 <div class="chart-area">
					<canvas id="LineChart"></canvas>
				</div>
				<hr />
				The bars are split by location; the line chart is the total income. (based on payed bills)
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				Vet Revenue
			</div>
			<div class="card-body">
                 <div class="chart-area">
					<canvas id="DonutChart"></canvas>
				</div>
				<hr />
				Total income split per vet, last 12 months;
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-8">
		<div class="card shadow mb-4">
			<div class="card-header">
				Average consult
			</div>
			<div class="card-body">
                 <div class="chart-area">
					<canvas id="LineChartAvg"></canvas>
				</div>
				<hr />
				Average price of bill, per vet; monthly.
			</div>
		</div>
	</div>
	<div class="col-md-4">
	</div>
</div>

<h1 class="h3 mb-2 text-gray-800">Clients</h1>
<hr>

<div class="row"> 
	<div class="col-md-12">
		<div class="card shadow mb-4">
			<div class="card-header">
				Report / Last Bill
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col">
						 <div class="chart-area">
							<canvas id="last_bill_line_chart"></canvas>
						</div>
					</div>
					<div class="col-lg-4">
							Clients last bill can be an indication of "active clients";<br /><br />
							<table class="table">
								<tr>
									<td>Last 2 Years</td>
									<td><?php echo array_sum(array_slice($last_bill_chart['ori_line'], -2)); ?></td>
								</tr>
								<tr>
									<td>Last 3 Years</td>
									<td><?php echo array_sum(array_slice($last_bill_chart['ori_line'], -3)); ?></td>
								</tr>
								<tr>
									<td>Last 5 Years</td>
									<td><?php echo array_sum(array_slice($last_bill_chart['ori_line'], -5)); ?></td>
								</tr>
							</table>
					</div>
				</div>
			</div>
		</div>
	</div>		
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	var bar_chart_color = ["rgba(255, 99, 132, 0.4)", "rgba(255, 159, 64, 0.4)", "rgba(255, 205, 86, 0.4)", "rgba(54, 162, 235, 0.4)"];
	var bar_chart_bgcolor = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)"];
	
	// var bar_chart_color_lb = ["rgba(255, 99, 132, 0.4)", "rgba(255, 159, 64, 0.4)", "rgba(255, 205, 86, 0.4)", "rgba(54, 162, 235, 0.4)"];
	// var bar_chart_bgcolor_lb = ["rgb(255, 99, 132)", "rgb(255, 159, 64)", "rgb(255, 205, 86)", "rgb(75, 192, 192)"];
	
	var bar_chart_color_lb = ["rgba(255, 99, 132, 0.8)","rgba(75, 192, 192, 0.8)","rgba(255, 205, 86, 0.8)","rgba(54, 162, 235, 0.8)","rgba(78, 115, 223, 0.8)","rgba(205, 56, 150, 0.8)",];
	var bar_chart_bgcolor_lb = ["rgba(255, 99, 132, 0.8)","rgba(75, 192, 192, 0.8)","rgba(255, 205, 86, 0.8)","rgba(54, 162, 235, 0.8)","rgba(78, 115, 223, 0.8)","rgba(205, 56, 150, 0.8)",];
	
	var avg_chart_color = ["rgba(255, 99, 132, 0.8)","rgba(75, 192, 192, 0.8)","rgba(255, 205, 86, 0.8)","rgba(54, 162, 235, 0.8)","rgba(78, 115, 223, 0.8)","rgba(205, 56, 150, 0.8)",];
	var avg_chart_color_border = ["rgba(255, 99, 132, 0.8)","rgba(75, 192, 192, 0.8)","rgba(255, 205, 86, 0.8)","rgba(54, 162, 235, 0.8)","rgba(78, 115, 223, 0.8)","rgba(205, 56, 150, 0.8)",];
	var data_avg_consult = {datasets: [
							<?php foreach ($avg_per_vet as $vet => $avgdata): ?>
								{ 
									data:<?php echo json_encode($avgdata) ?>,  
									label: '<?php echo $vet; ?>',
									backgroundColor: avg_chart_color.pop(),
									borderColor: avg_chart_color_border.pop(),
									fill: false,
								},	
							<?php endforeach; ?>
							]};
	var data_last_bill = { 
						
						datasets: [{ 
									type: 'line',
									data:<?php echo json_encode($last_bill_chart['line']) ?>,  
									label: "Last Bill",
									backgroundColor: "rgba(255, 99, 132, 0.95)",
									borderColor: "rgba(255, 99, 132, 0.95)",
									fill: false,
								},
								<?php foreach ($last_bill_chart['bar'] as $name => $bar_chart): ?>
								{ 
									type: 'bar',
									data:<?php echo json_encode($bar_chart) ?>,  
									label: '<?php echo $name; ?>',
									backgroundColor: bar_chart_color_lb.pop(),
									borderColor: bar_chart_bgcolor_lb.pop(),
									borderWidth:1
								},
								<?php endforeach; ?>
								] 
						};
	var data_line = { 
						
						datasets: [{ 
									type: 'line',
									data:<?php echo json_encode($last_6_month['line']) ?>,  
									label: "Total Earning",
									backgroundColor: "rgba(255, 99, 132, 0.95)",
									borderColor: "rgba(255, 99, 132, 0.95)",
									fill: false,
								},
								<?php foreach ($last_6_month['bar'] as $name => $bar_chart): ?>
								{ 
									type: 'bar',
									data:<?php echo json_encode($bar_chart) ?>,  
									label: '<?php echo $name; ?>',
									backgroundColor: bar_chart_color.pop(),
									borderColor: bar_chart_bgcolor.pop(),
									borderWidth:1
								},
								<?php endforeach; ?>
								] 
						};
	var data_donut = {
			labels: <?php echo json_encode($income_per_vet['vets']); ?>, 
			datasets: [{
					data:<?php echo json_encode($income_per_vet['income']); ?>,  
					"backgroundColor" :[
										"rgba(255, 99, 132, 0.8)",
										"rgba(75, 192, 192, 0.8)",
										"rgba(255, 205, 86, 0.8)",
										"rgba(54, 162, 235, 0.8)",
										"rgba(78, 115, 223, 0.8)",
										"rgba(205, 56, 150, 0.8)",
									]
			}]
	};
		
	/* LAST BILL */
	var ctx = document.getElementById("last_bill_line_chart");
	var myLineChart = new Chart(ctx, {
		type: 'bar',
		data: data_last_bill,
		
		options: {
			legend: {
				display: true,
				position: 'right',
				
			},	
			responsive: true,
			maintainAspectRatio: false,
			scales: {
				xAxes: [{
					stacked: true,
					type: 'time',
					time: {
						unit: 'year'
					}
				}],
				yAxes: [{
					stacked: true
				}]
			},
		tooltips: {
		  backgroundColor: "rgb(255,255,255)",
		  bodyFontColor: "#858796",
		  borderColor: '#dddfeb',
		  borderWidth: 1,
		  displayColors: false,
		},

		}
	});	
	
	/* TOTAL REVENUE */
	var ctx = document.getElementById("LineChart");
	var myLineChart = new Chart(ctx, {
		type: 'bar',
		data: data_line,
		
		options: {
			legend: {
				display: true,
				position: 'right',
				
			},	
			responsive: true,
			maintainAspectRatio: false,
			scales: {
				xAxes: [{
					stacked: true,
					type: 'time',
					time: {
						unit: 'month',
					  parser: 'YYYY.MM'
					}
				}],
				yAxes: [{
					stacked: true
				}]
			},
		tooltips: {
		  backgroundColor: "rgb(255,255,255)",
		  bodyFontColor: "#858796",
		  borderColor: '#dddfeb',
		  borderWidth: 1,
		  displayColors: false,
		  callbacks: {
			label: function (tooltipItem, data) {
				return data.datasets[tooltipItem.datasetIndex].label + ' : ' 
						+ new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].y);
				}
			}
		},

		}
	});

	var avg_line = document.getElementById("LineChartAvg");
	var myLineChart = new Chart(avg_line, {
		type: 'line',
		data: data_avg_consult,
		
		options: {
			legend: {
				display: true,
				position: 'right',
				
			},	
			responsive: true,
			maintainAspectRatio: false,
			scales: {
				xAxes: [{
					type: 'time',
					time: {
						unit: 'month',
					  parser: 'YYYY.MM'
					}
				}],
			},
		tooltips: {
		  backgroundColor: "rgb(255,255,255)",
		  bodyFontColor: "#858796",
		  borderColor: '#dddfeb',
		  borderWidth: 1,
		  displayColors: false,
		  callbacks: {
			label: function (tooltipItem, data) {
				return data.datasets[tooltipItem.datasetIndex].label + ' : ' 
						+ new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].y);
				}
			}
		},

		}
	});
	
	var ctxa = document.getElementById("DonutChart");
		// ctxa.height = 250;
var myPieCharta = new Chart(ctxa, {
  type: 'doughnut',
  data: data_donut,
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
	  callbacks: {
        label: function (tooltipItem, data) {
            return data.labels[tooltipItem.index] + ' : ' + new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
			}
		}
    },
    legend: {
      display: true,
	  position: 'right',
    },
    cutoutPercentage: 75,
  },
});
});

</script>