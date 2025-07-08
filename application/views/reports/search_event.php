<style>
    .dtr-data p {
  margin: 0.2em 0;
  line-height: 1.2;
}
#loading-screen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
</style>

<div id="loading-screen">
  <i class="fa-solid fa-rotate fa-spin fa-3x"></i>
</div>

<div class="row">
      <div class="col-lg-12">

      <div class="card shadow mb-4">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div><a href="<?php echo base_url('report/'); ?>"><?php echo $this->lang->line('Reports'); ?></a> / <?php echo $this->lang->line('title_search'); ?></div>
            </div>
            <div class="card-body">
                <form action="<?php echo base_url('report/search/'); ?>" method="post" autocomplete="off" class="form-inline">

                <div class="form-group mr-2">
                    <label for="search_query" class="sr-only">search_from</label>
                    <input type="text" class="form-control <?php echo ($search_query) ? 'is-valid' : ''; ?>" name="search_query" id="search" placeholder="<?php echo $this->lang->line('search_query'); ?>" value="<?php echo ($search_query) ? $search_query : ''; ?>" aria-describedby="searchHelp">
                </div>
                   
                <div class="dropdown mr-2">
                    <button class="btn btn-outline-secondary dropdown-toggle noarrow form-control" id="filter" type="button" data-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-sliders"></i></button>
                    <div class="dropdown-menu">
                        <div class="px-3 py-3">
                            <div class="checkbox-menu allow-focus p-3">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="anamnese" name="anamnese">
                                <label class="form-check-label" for="anamnese">Include anamnese</label>
                            </div>
                            <hr />
                            <div class="form-group py-1">
                                <label for="search_from">From</label>
                                <input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ?: ''; ?>" id="search_from">
                            </div>
                            <div class="form-group py-1">
                                <label for="search_to">To</label>
                                <input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ?: ''; ?>" id="search_to">
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

				  <button type="submit" name="submit" value="usage" class="btn btn-success"><?php echo $this->lang->line('search_range'); ?></button>
				</form>

                <p>
                <small><a href="#" id="toggleHelp">[help]</a></small>
                <div id="searchHelpText" class="text-muted border border-secondary" style="display:none; margin-top: 0.5rem;padding: 0.5rem; border-radius: 0.25rem;">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                            <th>Example search</th>
                            <th>What it finds</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <td>+fever +dog</td>
                            <td>Reports with both “fever” and “dog”</td>
                            </tr>
                            <tr>
                            <td>cough -cat</td>
                            <td>Reports with “cough” but <strong>not</strong> “cat”</td>
                            </tr>
                            <tr>
                            <td>"vomiting blood"</td>
                            <td>Exact phrase “vomiting blood”</td>
                            </tr>
                            <tr>
                            <td>allerg*</td>
                            <td>Words like “allergy”, “allergic”</td>
                            </tr>
                            <tr>
                            <td>dog (cough OR sneeze)</td>
                            <td>“dog” with “cough” or “sneeze”</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </p>

                <hr />
                <?php if($reports): ?>
				<table class="table table-sm" id="dataTable">
                    <thead>
                        <tr>
                            <th>date</th>
                            <th>title</th>
                            <th>anamnese</th>
                            <th>pet</th>
                            <th>owner</th>
                            <th>location</th>
                            <th>vet</th>
                            <th class="none">anamnese</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td data-sort="<?php echo strtotime($report['created_at']); ?>"><?php echo user_format_date($report['created_at'], $user->user_date); ?></td>
                            <td <?php echo $report['title_match'] ? "style='background-color:#EEEEEE;'": ""; ?>><a href="<?php echo base_url('events/event/' . $report['id']); ?>"><?php echo $report['title']; ?></a></td>
                            <td <?php echo !$report['title_match'] ? "style='background-color:#EEEEEE;'": ""; ?>><button class="btn btn-sm btn-outline-primary ana"><i class="fa-solid fa-eye"></i></button></td>
                            <td><a href="<?php echo base_url('pets/fiche/' . $report['pet_id']); ?>"><?php echo get_symbol($report['pet_type']) . $report['pet_name']; ?></a></td>
                            <td><a href="<?php echo base_url('owners/detail/' . $report['owner_id']); ?>"><?php echo $report['owner_name']; ?></a></td>
                            <td><?php echo $report['loc_name'] ?></td>
                            <td><?php echo $report['vet_name'] ?></td>
                            <td><?php echo nl2br ($report['anamnese']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

		    </div><!-- card-body -->
        </div><!-- card -->
	</div><!-- col-lg-12 -->
</div><!-- row -->


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
    <?php if($search_query != ""): ?>
        $('#loading-screen').show();
        setTimeout(function(){
            $('#loading-screen').fadeOut(300);
        }, 300);
    <?php else: ?>
        $('#loading-screen').hide();
    <?php endif; ?>
	var table = $("#dataTable").DataTable({
		"pageLength": 50,
		"order": [[ 0, "desc" ]],
		dom: "<'row'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'><'col-sm-12 col-md-7'p>>",
        responsive: {
            details: {
                type: 'column',
                target: 'button'
            }
        },
        columnDefs: [ {
            className: 'dtr-control',
            orderable: false,
            targets: -1
        },
	 ]
	});
		
    $('#toggleHelp').on('click', function(e) {
        e.preventDefault();
        $('#searchHelpText').toggle();
    });
});
</script>
