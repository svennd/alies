<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>Stats</div>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('stats'); ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mr-3">
					<label for="search_from" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				  </div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="search_to" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				  </div>
				  <div class="form-group mb-2 mx-3">
                    <select class="form-control" id="query" name="query">
                        <?php foreach($stats as $stat): ?>
                        <option value="<?php echo $stat['id']; ?>" <?php echo ($stat_info && $stat_info['id'] == $stat['id']) ? "selected" : ""; ?>><?php echo $stat['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
				  </div>
				  <button type="submit" name="submit" value="usage" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>
				<br>
                <?php if($stat_info): ?>
                    results for <strong><?php echo $stat_info['title']; ?></strong><br/><br/>
                <?php endif; ?>
                <?php if($result): ?>
				<table class="table table-sm" id="dataTable">
                <thead>
                    <tr>
                        <?php foreach($result[0] as $key => $value): ?>
                            <th><?php echo $key; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($result as $row): ?>
                        <tr>
                            <?php foreach($row as $value): ?>
                            <td><?php echo ($value); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
                <?php else: ?>
                    no results;
                <?php endif; ?>
                </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#dataTable").DataTable({
		responsive: true,
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
            { 
                extend:'excel', 
                text:'<i class="fas fa-file-export"></i> Excel', 
                className:'btn btn-outline-success btn-sm',
                autoFilter: true,
                sheetName:'stats_alies'
            },
        ]
    });
});
</script>
