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
				<small>Exports created & updated clients</small><br/><br/>
        <a href="<?php echo base_url(); ?>reports/clients/7" class="btn btn-success"><i class="fas fa-user-edit"></i> 7 days</a>
        <a href="<?php echo base_url(); ?>reports/clients/14" class="btn btn-success"><i class="fas fa-user-edit"></i> 14 days</a>
        <a href="<?php echo base_url(); ?>reports/clients/30" class="btn btn-success"><i class="fas fa-user-edit"></i> 30 days</a>
        <br/>
				<small>Lookup created & updated clients.</small><br/><br/>
			</div>
		</div>
	</div>
</div>



<?php if(isset($days) && $clients): ?>
  <div class="row">
	<div class="col-md-12">
  <div class="card shadow mb-4">
			<div class="card-header">
				Modified clients last <?php echo $days; ?> days.
			</div>

			<div class="card-body">
        <table class="table" id="datatable">
					<tr>
						<td>Name</td>
						<td>Last Bill</td>
						<td>Street</td>
						<td>City</td>
					</tr>
				<?php foreach ($clients as $client): ?>
					<tr>
						<td><?php echo $client['last_name']; ?></td>
						<td><?php echo ($client['last_bill']) ? $client['last_bill'] : '-'; ?></td>
						<td><?php echo ($client['street']) ? $client['street']. ' ' .$client['nr'] : '-'; ?></td>
						<td><?php echo ($client['city']) ? $client['city'] : '-'; ?></td>
					</tr>
				<?php endforeach; ?>
        </table>
        </div>
		</div>
		</div>
</div>

<?php else: ?>
<div class="row">
	<div class="col-md-8">
  <div class="card shadow mb-4">
			<div class="card-header">
				Last bill chart
			</div>

			<div class="card-body">
        <canvas id="last_bill_chart"></canvas>
        <p class="small">Chart : The data of last bill, of customers over a period of 10y; split over the initial vet.</p>
	
			</div>
		</div>
		</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<?php endif; ?>