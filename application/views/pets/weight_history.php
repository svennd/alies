  <div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pets['owners']['id']; ?>"><?php echo $pets['owners']['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pets['id']; ?>"><?php echo $pets['name'] ?></a> / Weight
			</div>
            <div class="card-body">
				<canvas id="chart1" style="height:20vh; width:40vw"></canvas>
			<br>
			<hr>
			<br>
			<div class="row">
				<div class="col-lg-6">
			<?php if ($weight_history): ?>
			<div class="table-responsive">
				<table class="table">
				<thead>
				<tr>
					<th>date</th>
					<th>weight</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($weight_history as $weight): ?>
				<tr>
					<td><?php echo $weight['created_at'] . " (" . timespan(strtotime($weight['created_at']), time(), 1) . ")"; ?></td>
					<td><?php echo $weight['weight']; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
				</div>
			<?php endif; ?>
                </div>
				<div class="col-lg-6">
						<form action="<?php echo base_url(); ?>pets/add_weight/<?php echo $pets['id'] ?>" method="POST">
						  <div class="card card-body">
						  New Weight:
						  <div class="form-row">
							<div class="col-3">
							  <input type="text" name="weight" class="form-control" />
							</div>
							
						   <button type="submit" name="submit" value="1" class="btn btn-primary">Add</button>
						  </div>
							</div>
						</form>
                </div>
			</div>
		</div>

            </div>
		</div>
			      
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){
<?php
if (isset($weight_history))
{
	foreach ($weight_history as $weight)
	{
		$plot_data[] = $weight['weight'];
		$plot_label[] = timespan(strtotime($weight['created_at']), time(), 2);
	}
}
else
{
	$plot_data[] = array();
	$plot_label[] = array();
}
?>

	var ctx = document.getElementById('chart1').getContext('2d');

	var chart = new Chart(ctx, {
		type: 'line',
		data: { 
				labels: <?php echo json_encode($plot_label); ?>,
				datasets: [{
					label : "weight",
					backgroundColor: 'rgba(54, 162, 235, 0.3)',
					borderColor : 'rgb(54, 162, 235)',
					borderWidth: 1,
					data: <?php echo json_encode($plot_data); ?>,
				}],
			},
			options: {
				
				responsive: true,
					legend: {
							display : false,
						},
				scales: {
					yAxes: [{
						display : true,
							scaleLabel: {
								display: true,
								labelString: 'Kg'
							},
						ticks: {
							beginAtZero: false
						}
					}]
				}
			}
	});
});
</script>