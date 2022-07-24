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
<div class="row">
	<div class="col-md-5">
		<canvas id="last_bill_chart"></canvas>
		<p class="small">Chart : The data of last bill, of customers over a period of 10y; split over the initial vet.</p>
	</div>
</div>

<script>
const ctx = document.getElementById("last_bill_chart").getContext("2d");

const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
const randomRGB = () => `rgb(${randomNum()}, ${randomNum()}, ${randomNum()})`;

const data = {
  labels: [
    <?php echo implode(',', $last_bill_clients['years']); ?>
  ],
  datasets: [
	<?php foreach($last_bill_clients['data'] as $vet => $data): ?>
    {
      label: "<?php echo $vet; ?>",
      backgroundColor: randomRGB(),

      data: [
        <?php echo implode(',', $data); ?>
      ]
    },
	<?php endforeach; ?>
  
  ]
};

const options = {
	plugins: {
      title: {
        display: true,
        text: 'Last bill date'
      },
    },
    responsive: true,
    scales: {
      x: {
        stacked: true,
      },
      y: {
        stacked: true
      }
    }
};

const chart = new Chart(ctx, {
  // The type of chart we want to create
  type: "bar",
  // The data for our dataset
  data: data,
  // Configuration options go here
  options: options
});

</script>
