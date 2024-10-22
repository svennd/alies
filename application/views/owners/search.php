<style>
.card-waves .card-body {
    background-image: url("<?php echo base_url('/assets/img/bg-waves.svg'); ?>");
}
</style>

<!-- normal button : on small screens -->
<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user"></i> <?php echo $this->lang->line('New_client'); ?></a>

<div class="row">
	
	<div class="col-lg-10">
		<div class="card card-waves shadow mb-4">
			<div class="card-body px-3 pt-3 pb-0">
				<form action="<?php echo base_url('search'); ?>" method="get" autocomplete="off">
				<div class="row align-items-center justify-content-between px-3">
					<div class="col-lg-8">
						<div class="shadow rounded">
						  <div class="form-group has-search">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control <?php echo (isset($query)) ? 'is-valid' :''?>" name="search_query" placeholder="search" value="<?php echo (isset($query)) ? $query :''?>">
								<div class="input-group-append">
								  <button class="btn btn-primary" type="submit" type="button">
									<div class="d-none d-sm-block"><?php echo $this->lang->line('title_search'); ?></div>
									<div class="d-block d-sm-none d-md-none">S</div>
								  </button>
								</div>
							</div>
						  </div>
						</div>
					</div>
				</div>
				</form>
				<?php if (isset($query)) : ?>
					<nav class="nav nav-borders">
						<?php 
							$results = array(
													array($last_name, $this->lang->line('last_name'), 'last_name'), 
													array($first_name, $this->lang->line('first_name'), 'first_name'), 
													array($street, $this->lang->line('street'), 'street'), 
													array($pets, $this->lang->line('pets'), 'pets'), 
													array($phone, $this->lang->line('phone'), 'phone'),
													array($breeds, $this->lang->line('breed'), 'breed')
													);
							$active_switch = false;
							$total_count = 0;
							foreach ($results as $key => $value) 
							{
								$current_count = count($value[0]);
								$total_count += $current_count;
								
								$current_key = $key + 1; // 0 --> all
								
								if (!$current_count) {continue;}
								
								if ($user->search_config == $current_key)
								{
									$active_switch = true;
									echo '<a href="#" class="nav-link filter_type active" id="' . $value[2] . '">' . $value[1] . ' <span class="badge badge-primary">' . $current_count . '</span></a>';
								}
								else
								{
									echo '<a href="#" class="nav-link filter_type" id="' . $value[2] . '">' . $value[1] . ' <span class="badge badge-primary">' . $current_count . '</span></a>';
								}
							}
							echo '<a href="#" class="nav-link ' . ((!$active_switch) ? "active" : "") .' reset" id="reset_filter">' . $this->lang->line('search_all') . ' <span class="badge badge-primary">' . $total_count . '</span></a>';
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>					
	</div>	
	<!-- large button : on large screens -->
	<div class="col-lg-2 mb-4">
		<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success btn-lg mb-3 d-none d-sm-block bounceit"><i class="fa-solid fa-fw fa-user-plus"></i> <?php echo $this->lang->line('New_client'); ?></a>
	</div>
</div>

<div class="row">

	<div class="col-lg-12">
	<?php if (isset($query)): ?>
		<div class="card shadow mb-4">
			<!-- <div class="card-header"><?php echo $this->lang->line('search_client'); ?></div> -->
			<div class="card-body">
				<table class="table table-sm table-search display dt-responsive nowrap" width="100%" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('last_name'); ?></th>
					<th><?php echo $this->lang->line('first_name'); ?></th>
					<th><?php echo $this->lang->line('adress'); ?></th>
					<th>nr</th>
					<th><?php echo $this->lang->line('city'); ?></th>
					<?php if (count($phone)): ?>
					<th><?php echo $this->lang->line('phone'); ?></th>
					<?php endif; ?>
					<th><?php echo $this->lang->line('last_visit'); ?></th>
				</tr>
				</thead>
				<tbody>

				<?php foreach (array(
						"last_name" 	=> $last_name, 
						"first_name" 	=> $first_name,
						"street"		=> $street,
						"pets"			=> $pets,
						"phone"			=> $phone,
						"breeds"		=> $breeds,

						) as $key => $current): ?>
					
					<?php foreach ($current as $res):?>
						<tr>
							<td>								
								<a href="<?php echo base_url('owners/detail/' . $res['id']); ?>" class="text-nowrap 
																					<?php echo ($res['debts']) ? "search-owners-debts" : ""; ?>											
																					<?php echo ($res['low_budget']) ? "search-owners-low-budget" : ""; ?>											
										">
					
										<?php if($res['disabled']): ?><s><?php endif; ?>
											<?php echo $res['last_name']; ?>
										<?php if($res['disabled']): ?></s><?php endif; ?>
								</a> 
								<?php if ($key == "pets"): ?>
									<small>(<?php echo get_symbol($res['type']); ?><?php echo $res['name'] ?>)</small>
								<?php elseif ($key == "breeds"): ?>
									<small>(<?php echo get_symbol($res['type']); ?><?php echo $res['name'] ?>, <strong><?php echo $res['breed']; ?></strong>)</small>
								<?php endif; ?>
							</td>
							<td><?php echo $res['first_name']; ?></td>
							<td><?php echo $res['street']; ?></td>
							<td><?php echo $res['nr']; ?></td>
							<td><?php echo $res['city']; ?></td>
							<?php if (count($phone)): ?>
							<td>
								<?php echo (!empty($res['telephone'])) ? print_phone($res['telephone']) . '<br/>' : ''; ?>
								<?php echo (!empty($res['mobile'])) ? print_phone($res['mobile']) . '<br/>' : ''; ?>
								<?php echo (!empty($res['phone2'])) ? print_phone($res['phone2']) . '<br/>' : ''; ?>
								<?php echo (!empty($res['phone3'])) ? print_phone($res['phone3']) . '<br/>' : ''; ?>
							</td>
							<?php endif; ?>
							<td data-filter="<?php echo $key; ?>" data-sort="<?php echo is_null($res['last_bill']) ? 0 : strtotime($res['last_bill']); ?>"><?php echo user_format_date($res['last_bill'], $user->user_date); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>

			</tbody>
			</table>
		<?php endif; ?>
		</div>
		</div>
	</div>				
</div>				

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#home").addClass('active');
	
	var table = $("#dataTable");
	var key_column = $(table).find('th:last').index();

	var dt = table.DataTable({
		scrollY: '63vh',
		deferRender:    true,
		scroller:       true,
		"responsive": {
        	"details": {
            "type": 'column',
            "target": 'tr'
        }
  	  },
	  "order": [[ key_column, 'desc' ]]
	});
	
	// initial filter if required
	if($('.filter_type.active').attr('id')) {
		dt
		.column(key_column)
        .search($('.filter_type.active').attr('id'))
        .draw();
	}
	
    $('.filter_type').on('click', function() {
	  // remove color from filter
	  $('#reset_filter').removeClass('active');
	  
	  // reset all colors
	  $('.filter_type').removeClass('active');
	  
	  // add success to current filter
	  $(this).removeClass('btn-info').addClass('active')
	  
      dt
		.column(key_column)
        .search($(this).attr('id'))
        .draw();
    });

    $('.reset').on('click', function() {
		
	  // reset all colors
	  $('.filter_type').removeClass('active');
	  
	  // color reset
	  $(this).addClass('active');
		
      dt
		.column(key_column)
        .search('')
        .draw();
    });
});
</script>
