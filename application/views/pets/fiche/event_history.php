<style>
/* table */
.table th {
    font-weight: 500;
    color: #827fc0;
}
.table thead {
    background-color: #f3f2f7;
}
.table>tbody>tr>td, .table>tfoot>tr>td, .table>thead>tr>td {
    padding: 14px 12px;
    vertical-align: middle;
}
.table tr td {
    color: #8887a9;
}
</style>
<div class="card shadow mb-4">

	<?php if(!isset($full_history)): ?>
	<div class="card-header"><a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>">History</a></div>
	<?php else : ?>	
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
			<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo $pet['name'] ?></a> / History
		</div>
	</div>
	<?php endif; ?>
	<div class="card-body">
		
		<?php if (isset($full_history)): ?>
		<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
			<div class="btn-group btn-group-sm mr-2" role="group" aria-label="Basic example">
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/1" role="button"><i class="fas fa-fw fa-file-medical"></i></a>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/2" role="button"><i class="fas fa-fw fa-syringe"></i></a>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/3" role="button"><i class="fas fa-fw fa-tooth"></i></a>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/4" role="button"><i class="fas fa-fw fa-hammer"></i></a>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/5" role="button"><i class="fas fa-fw fa-heartbeat"></i></a>
				<?php if($show_no_history == 'no_history'): ?>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>">
					<i class="fas fa-eye"></i>
				</a>
				<?php else: ?>
				<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>/no_history">
					<i class="fas fa-eye-slash"></i>
				</a>
				<?php endif; ?>
			</div>
			
			<div class="input-group input-group-sm">
				<div class="input-group-prepend">
					<div class="input-group-text" id="btnGroupAddon"><i class="fas fa-search"></i></div>
				</div>
				<input type="text" class="form-control" id="title_search" onkeyup="search_history()" value="" placeholder="Search Titles">
			</div>
		</div>
			
			<br/>
			<br/>
		<?php endif; ?>
		
		<?php if ($pet_history): ?>
		<table class="table table-hover mb-0" id="pet_history">
		<thead>
			<tr class="align-self-center">
				<th>Type</th>
				<th>Title</th>
				<th><i class="far fa-clock"></i>  Date</th>
				<th><i class="fas fa-user-md"></i> Vet</th>
				<th><i class="fas fa-compass"></i> Location</th>
				<th>Anamnese</th>
			</tr>
		</thead>
	<?php
	$symbols = array(
			"fas fa-file-medical",
			"fas fa-syringe",
			"fas fa-tooth",
			"fas fa-hammer",
			"fas fa-heartbeat",
		);
		
		for ($i = 0; $i < count($pet_history); $i++) :  
		
			$history = $pet_history[$i]; 
			$products = (isset($pet_history[$i]['products'])) ? $pet_history[$i]['products']: array();
			$procs = (isset($pet_history[$i]['procedures'])) ? $pet_history[$i]['procedures']: array();
	?>
	<tr class="searchable">
		<td><div class="humb-sm rounded-circle mr-2"><i class="<?php echo $symbols[$history['type']]; ?>"></i></div></td>
		<td><?php echo $history['title']; ?></td>
		<td><?php echo substr($history['created_at'], 0, 10); ?></td>
		<td><?php echo (isset($history['vet']['first_name'])) ? $history['vet']['first_name'] : 'unknown soldier' ; ?></td>
		<td><?php echo (isset($history['location']['name'])) ? $history['location']['name'] : "unknown"; ?></td>
		<td>
			<div id="anamnese_<?php echo $i; ?>" class="btn btn-outline-secondary ana">show</div>
			<a href="<?php echo base_url(); ?>events/event/<?php echo $history['id']; ?>" class="btn btn-outline-secondary">edit</a></div>
		</td>
	</tr>
	<tr id="anamnese_<?php echo $i; ?>_text" style="display:none;">
		<td colspan="3"><?php echo nl2br ($history['anamnese']); ?></td>
		<td colspan="3" style="border-left:1px solid #e3e6f0;">
			<ul>
			<?php foreach($products as $prod) : ?>
				<li><?php echo $prod['volume'] . ' ' . $prod['unit_sell']  . ' ' . $prod['name']; ?></li>
			<?php endforeach; ?>
			<?php foreach($procs as $proc) : ?>
				<li><?php echo $proc['amount'] . ' ' . $proc['name']; ?></li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	<?php endfor; ?>
		<?php if(!isset($full_history)): ?>
		<tr>
			<td colspan="6" class="text-center"><a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>" class="btn btn-outline-secondary">Full History (<?php echo $history_count; ?>)</a></td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<?php else : ?>
	No history yet.
	<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
	// no datatable possible due to hidden anamnese & sold products;
	// source : https://www.w3schools.com/howto/howto_js_filter_table.asp
	function search_history() {
	  // Declare variables
	  var input, filter, table, tr, td, i, txtValue;
	  input = document.getElementById("title_search");
	  filter = input.value.toUpperCase();
	  table = document.getElementById("pet_history");
	  tr = table.getElementsByClassName("searchable");

	  // Loop through all table rows, and hide those who don't match the search query
	  for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[1]; // title
		if (td) {
		  txtValue = td.textContent || td.innerText;
		  if (txtValue.toUpperCase().indexOf(filter) > -1) {
			tr[i].style.display = "";
		  } else {
			tr[i].style.display = "none";
		  }
		}
	  }
	}	
	
document.addEventListener("DOMContentLoaded", function(){
	// history anamnese
	$(".ana").click(function(){	
		$("#" + this.id + "_text").toggle();
	});
	
	


});
</script>
