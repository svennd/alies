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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div>
  <canvas id="myChart"></canvas>
</div>

<script>
	const data = {
	  datasets: [
			{
	    label: 'My First Dataset',
			data: {
		January: 10,
		February: 20,
		march: 25,
},
	    fill: false,
	    borderColor: 'rgb(75, 192, 192)',
	    tension: 0.1
	  },
			{
	    label: 'My second Dataset',
			data: {
		January: 15,
		February: 8,
		march: 25,
},
	    fill: false,
	    borderColor: 'rgb(75, 192, 192)',
	    tension: 0.1
	  },

	]
	};
  const config = {
    type: 'line',
    data: data,
    options: {}
  };

  const myChart = new Chart(
    document.getElementById('myChart'),
    config
  );
</script>
