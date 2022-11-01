
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>breeds/index"><?php echo $this->lang->line('breeds'); ?></a> / detail
			</div>
			<div class="card-body" style="height:40vh; width:80vw">
  				<canvas id="myChart" style="width:100%; height:100%;"></canvas>
			</div>
			<hr>
			<div class="card-body">
			<p><?php echo $this->lang->line('only_showing_alive_not_lost'); ?> : <?php echo count($pets) ;?> / <?php echo isset($breed['pets'][0]['counted_rows']) ? $breed['pets'][0]['counted_rows'] : 0; ?></p>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('pets'); ?></th>
					<th><?php echo $this->lang->line('client'); ?></th>
					<th><?php echo $this->lang->line('adress'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($pets as $res): ?>
				<tr>
					<td>
						<a href="<?php echo base_url() . 'pets/fiche/' . $res['id']; ?>" class="text-nowrap">
							<?php echo $res['name']; ?>
						</a>
					</td>
					<td><a href="<?php echo base_url() . 'owners/detail/' . $res['owners']['id']; ?>"><?php echo $res['owners']['last_name']; ?></a></td>
					<td>
					<div class="row">
						<div class="col">
						<?php echo $res['owners']['street']; ?>
						</div>
						<div class="col">
						<?php echo $res['owners']['city']; ?>
						</div>
					</div>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	
const data = {
  datasets: [
	{
		label: 'female',
		data: [
		<?php
		foreach ($stats as $stat)
		{
			if ($stat['gender'] == FEMALE) {
				echo "{x:" . $stat['age'] . ", y:" . $stat['last_weight'] . "}, ";
			}
		}
		?>],
		backgroundColor: 'rgb(255, 99, 132)'
	},
	{
		label: 'male',
		data: [
		<?php
		foreach ($stats as $stat)
		{
			if ($stat['gender'] == MALE) {
				echo "{x:" . $stat['age'] . ", y:" . $stat['last_weight'] . "}, ";
			}
		}
		?>],
		backgroundColor: 'rgb(99, 255, 132)'
	},
	{
		label: 'male neutered',
		data: [
		<?php
		foreach ($stats as $stat)
		{
			if ($stat['gender'] == MALE_NEUTERED) {
				echo "{x:" . $stat['age'] . ", y:" . $stat['last_weight'] . "}, ";
			}
		}
		?>],
		backgroundColor: 'rgb(132, 99, 99)'
	},
	{
		label: 'female neutered',
		data: [
		<?php
		foreach ($stats as $stat)
		{
			if ($stat['gender'] == FEMALE_NEUTERED) {
				echo "{x:" . $stat['age'] . ", y:" . $stat['last_weight'] . "}, ";
			}
		}
		?>],
		backgroundColor: 'rgb(255, 255, 132)'
	},

],
};

const config = {
  type: 'scatter',
  data: data,
  options: {
    scales: {
      x: {
        title: {
          display: true,
          text: 'Age (year)'
		},
		min: 0,
        type: 'linear',
        position: 'bottom'
      },
	  y: {
        title: {
          display: true,
          text: 'Weight (kg)'
		},
		min: 0,
	  }
    }
  }
};

const myChart = new Chart(
    document.getElementById('myChart'),
    config
  );
  
});
</script>