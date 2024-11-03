<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">

		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url('reports/products'); ?>">Reports</a> / Usage / <?php echo $prod_info['name']; ?></div>
			<div class="dropdown no-arrow">
				<a href="<?php echo base_url('reports/usage_csv/' . $prod_info['id'] . '/' . (($search_from) ? $search_from : '') . '/' . (($search_to) ? $search_to : '') ); ?>"class="btn btn-outline-info btn-sm"><i class="fas fa-file-export"></i> Export</a>
			</div>
		</div>
        <div class="card-body">
				<form action="<?php echo base_url('reports/usage/' . $prod_info['id']); ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mr-3">
					<label for="search_from" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				  </div>
				  <div class="form-group">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mx-2">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				  </div>

				  <div class="form-group mr-4">
					  <div class="dropdown">
						<button class="btn btn-outline-secondary dropdown-toggle noarrow" id="filter" type="button" data-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-sliders"></i></button>
						<ul class="dropdown-menu checkbox-menu allow-focus">
							<li><label><input type="checkbox" name="summary"> summary</label></li>
						</ul>
						</div>
					</div>

				  <button type="submit" name="submit" value="usage" class="btn btn-success">Search range</button>
				</form>
